<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use Frosting\IService\ObjectFactory\IObjectFactoryService;
use Frosting\IService\ObjectFactory\IObjectBuilder;
use Mandango\Mondator\Dumper;
use Frosting\IService\ObjectFactory\IClassCreator;
use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\Framework\Frosting;

/**
 * Description of ObjecFactory
 *
 * @author Martin
 */
class ObjectFactory implements IObjectFactoryService
{
  const AUTOGENERATED_NAMESPACE = 'autoGenerated';
  
  /**
   * @var \Frosting\IService\ObjectFactory\IObjectBuilder[]
   */
  private $builders = array();
  
  private $classCreators = array();
  
  private $generationPath;
  
  private $classModifications;
  
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private $serviceContainer = null;
  
  public function setServiceContainer(IServiceContainer $serviceContainer)
  {
    spl_autoload_register(array($this,'autoload'), true, true);
    $configuration = $serviceContainer->getServiceByName('configuration');
    $this->serviceContainer = $serviceContainer;
    $this->generationPath = $configuration->get('[configuration][generatedDirectory]');
    $this->debug = (bool)$configuration->get('[configuration][debug]');
  }
  
  public function autoload($class)
  {
    if(strpos($class, self::AUTOGENERATED_NAMESPACE . '\\') !== 0) {
      return false;
    }
    
    $classPath = $this->generationPath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    
    if($this->mustGenerateFile($classPath,$class)) {
      $this->generateClass(substr($class, strlen(self::AUTOGENERATED_NAMESPACE)), $class, $classPath);
    }
    
    require $classPath;
    return true;
  }
  
  private function mustGenerateFile($classPath, $class)
  {
    if(!file_exists($classPath)) {
      return true;
    }
   // return true;
    if(!$this->debug) {
      return false;
    }
    
    $parentClass = substr($class, strlen(self::AUTOGENERATED_NAMESPACE));
    return filemtime($classPath) < $this->getLastModificationDate($parentClass);
  }

  private function generateClass($baseClass,$extendClass,$classPath)
  {
    $classDefinition = new ChildClassDefinition($extendClass);
    $classDefinition->setParentClass($baseClass);
    
    foreach($this->classCreators as $creator) {
      $creator->modifyCode($classDefinition);
    }
  
    $classDefinition->finalize();
    
		$dumper = new Dumper($classDefinition);
    $dirName = dirname($classPath);
    if(!is_dir($dirName)) {
      if(!mkdir($dirName,0777,true)) {
        error_log('Cannot make ' . $dirName);
      }
    }

    file_put_contents($classPath, $dumper->dump());
    chmod($classPath, 0777);
  }
  
  private function getLastModificationDate($class)
  {
    $className = is_string($class) ? $class : $class->getName();

    if(isset($this->classModifications[$className])) {
      return $this->classModifications[$className];
    }
        
    $modificationDates = array();
    
    if(!($class instanceof \ReflectionClass)) {
      $class = new \ReflectionClass($class);
    }

    $modificationDates[] = filemtime($class->getFileName());
    $parentClass = $class->getParentClass();
    if($class->getParentClass()) {
      $modificationDates[] = $this->getLastModificationDate($parentClass);
    }
    
    foreach($class->getInterfaces() as $interface) {
      $modificationDates[] = $this->getLastModificationDate($interface);
    }
    
    return $this->classModifications[$class->getName()] = max($modificationDates);
  }
 
  
  /**
   * @return mixed 
   */
  public function createObject($class,array $constructorArguments = array(), $contextParameters = array())
  {
    $class = $this->getClassName($class);
    
    class_exists($class,true);

    $reflectionClass = new \ReflectionClass($class);
    if($reflectionClass->hasMethod('__selfFactory')) {
      $parameters = array_merge(array(null),$constructorArguments);
      $object = call_user_func_array(
        array($reflectionClass->getMethod('__selfFactory'),'invoke'),
        $parameters
      );
    } else {
      $object = $reflectionClass->newInstanceArgs($constructorArguments);
    }
    
    foreach($this->builders as $builder) {
      $builder->initializeObject($object, $contextParameters);
    }
    
    $className = get_class($object);
    if(is_callable($className . '::__factoryInitialization')) {
      call_user_func(array($className,'__factoryInitialization'),$object ,  $this->serviceContainer, $contextParameters);
    }
    
    return $object;
  }
  
  private function getClassName($class)
  {
    if(strpos($class, self::AUTOGENERATED_NAMESPACE . '\\') !== 0) {
      return self::AUTOGENERATED_NAMESPACE . "\\" . $class;
    }
    
    return $class;
  }
  
  /**
   * @param \Frosting\IService\ObjectFactory\IObjectBuilder $objectBuilder
   */
  public function registerObjectBuilder(IObjectBuilder $objectBuilder)
  {
    $this->builders[] = $objectBuilder;
  }
  
  public function registerClassCreator(IClassCreator $classCreator)
  {
    $this->classCreators[] = $classCreator;
  }
  
  /**
   * @param mixed $configuration
   * @return IObjectFactoryService
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,self::FROSTING_SERVICE_NAME);
  }
}
