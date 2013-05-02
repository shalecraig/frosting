<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\DependencyInjection\Generator\ContainerGenerator;

/**
 * Description of ServiceContainer
 *
 * @author Martin
 */
class ServiceContainer implements IServiceContainer
{
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer 
   */
  private $generatedContainer = null;
  
  protected function initiliaze() 
  {
    $objectFactory = $this->generatedContainer->getServiceByName("objectFactory");
    foreach($this->getServicesByTag("objectFactory.builder") as $service) {
      $objectFactory->registerObjectBuilder($service);
    }
  }
  
  public function shutdown() 
  {
    $this->generatedContainer->shutdown();  
  }
 
  public function getServiceByName($name)
  {
    return $this->generatedContainer->getServiceByName($name);
  }
  
  /**
   * Get all services that are tag
   * 
   * @param string $tag The tag the service must be done with
   * 
   * @return array of service objects 
   */
  public function getServicesByTag($tag) 
  {
    return $this->generatedContainer->getServicesByTag($tag);
  }

  public function getServiceNames() 
  {
    return $this->generatedContainer->getServiceNames();
  }
  
  public function getServiceConfiguration($name) 
  {
    return $this->generatedContainer->getServiceConfiguration($name);
  }
  
  public static function factory($configuration)
  {
    $neededServices = array(
      'serviceContainer' => get_called_class(),
      'annotationParser' => 'Frosting\Annotation\AnnotationParser',
      'objectFactory' => 'Frosting\ObjectFactory\ObjectFactory'
    );
    
    $services = array();
    
    foreach($neededServices as $serviceName => $instanceOf) {
      if(isset($configuration[$serviceName]['class'])) {
        $reflectionClass = new \ReflectionClass($configuration[$serviceName]['class']);
        if($reflectionClass->isSubclassOf($instanceOf)) {
          throw new \RuntimeException('The service named [' . $serviceName . '] must be a instance of [' . $instanceOf .']');
        }
        $services[$serviceName] = $reflectionClass->newInstance();
      } else {
        $services[$serviceName] = new $instanceOf();
      }
    }
    
    $services['annotationParser']->addNamespace(__NAMESPACE__);

    $generator = new ContainerGenerator($services['annotationParser'],$configuration);
    $path = sys_get_temp_dir();
    $className = $generator->generate($path, true);
    
    require_once($path . '/' . $className . '.php');
 
    $container = $services['serviceContainer'];
    
    $container->generatedContainer = new $className($services);
    $container->generatedContainer->getServiceByName("objectFactory")
      ->registerClassCreator(
        new \Frosting\ObjectFactory\AnnotationClassCreator($services['annotationParser'])
      );
    //$container->generatedContainer->getServiceByName("objectFactory")->registerObjectBuilder(new InjecterObjectBuilder($container));
    
    $services['objectFactory']->setServiceContainer($container);
    
    $container->initiliaze();
    return $container;
  }
}
