<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection\Generator;

use Frosting\IService\Annotation\IAnnotationParserService;
use Frosting\IService\FileSystem\IFileSystemService;

/**
 * Description of ContainerGenerator
 *
 * @author mpoirier
 */
class ContainerGenerator
{
  /**
   * @var \Mandango\Mondator\Definition\Definition 
   */
  private $classDefinition;
  
  private $configuration;
  
  private $servicesTags = array();
  
  /**
   * @var \Frosting\IService\Annotation\IAnnotationParserService
   */
  private $annotationParser;
  
  /**
   * @var \Frosting\IService\FileSystem\IFileSystemService
   */
  private $fileSystem;
  
  /**
   * @var \Frosting\IService\Annotation\IParsingResult
   */
  private $currentParsingResult;
  
  /**
   * @param array $servicesConfiguration
   */
  public function __construct(
    IAnnotationParserService $annotationParser, 
    IFileSystemService $fileSystem,      
    $servicesConfiguration)
  {
    $this->annotationParser = $annotationParser;
    $this->configuration = $servicesConfiguration;
    $this->fileSystem = $fileSystem;
  }
  
  public function getServiceConfiguration($serviceName)
  {
    foreach($this->configuration as $serviceConfiguration) {
      if($serviceConfiguration['name'] == $serviceName) {
        return $serviceConfiguration;
      }
    }
    
    return null;
  }
  
  /**
   * @param string $serviceName
   * @param string $tag
   */
  public function tagService($serviceName, $tag)
  {
    $this->servicesTags[$tag][] = $serviceName;
  }
  
  /**
   *
   * @return \Mandango\Mondator\Definition\Definition 
   */
  public function getClassDefinition()
  {
    return $this->classDefinition;
  }
  
  /**
   * @return \Mandango\Mondator\Definition\Method  
   */
  public function getInitializeMethod()
  {
    return $this->getMethodByName('initialize');
  }
  
  /**
   * @param type $serviceName
   * @return \Mandango\Mondator\Definition\Method 
   */
  public function getServiceGetterMethod($serviceName)
  {
    $serviceName = str_replace(".", "_", $serviceName);
    return $this->getMethodByName('getService_' . $serviceName);
  }
  
  private function getMethodByName($methodName)
  {
    try {
      $method = $this->classDefinition->getMethodByName($methodName);
    } catch (\InvalidArgumentException $e) {
      $method = new \Mandango\Mondator\Definition\Method('private', $methodName, '', '');
      $method->setCode('$serviceContainer = $this;');
      $this->classDefinition->addMethod($method);
    }
      
    return $method;
  }
  
  /**
   * @param boolean $checkChange Verify if some change have been made to config or file before loading it (must be false for production)
   */
  public function generate($generationPath, $checkChange = false)
  {
    $className = 'GeneratedContainer';
    if($checkChange) {
      $className .= '_' . md5(serialize($this->configuration));
    }
    
    $file = $generationPath . '/' . $className . '.php';
    if(!$this->fileSystem->exists($file) || ($checkChange && $this->mustBeRegenerated(filemtime($file)))) {
      
      $this->classDefinition = new \Mandango\Mondator\Definition\Definition($className);
      $this->classDefinition->addInterface('\Frosting\IService\DependencyInjection\IServiceContainer');
      $constructor = new \Mandango\Mondator\Definition\Method(
        'public', 
        '__construct', 
        '$services',
        '
        $this->services = $services;
      ');
      $this->classDefinition->addMethod($constructor);
      $method = $this->getInitializeMethod();
      $method->setVisibility('public');
      
      $startCodes = array();
      
      foreach ($this->configuration as $serviceName => $serviceConfiguration) {
        if(!isset($serviceConfiguration['class'])) {
          error_log('Error configuration service name [' . $serviceName . ']');
          continue;
        }
        $initializationConfiguration = isset($serviceConfiguration['configuration']) ? $serviceConfiguration['configuration'] : null;
        
        $getter = $this->getServiceGetterMethod($serviceName);
        
        $startCodes[$serviceName] = 
         "\$serviceContainer = \$this;
          \$service = \$this->getServiceByName('objectFactory')
            ->createObject(
              '" . $serviceConfiguration['class'] . "',
              array(),
              array(
                'serviceName'=>'$serviceName',
                'configuration'=>" . var_export($initializationConfiguration,true) . "
              )
          );
          \$this->services['" . $serviceName . "'] = \$service;
        ";
        $this->currentParsingResult = $this->annotationParser->parse($serviceConfiguration['class']);

        $annotations = $this->currentParsingResult->getAllAnnotations(array(
          function($annotation) {return $annotation instanceof IServiceContainerGeneratorAnnotation;}
        ));

        foreach($annotations as $annotationInfo) {
          $annotationInfo['annotation']->generateContainer($this, $serviceName, $annotationInfo['contextName']);
        }
      }
      
      $this->currentParsingResult = null;
      
      //We redo a loop so we are sure all service have been generated and we put
      //the return at the end
      foreach ($this->configuration as $serviceName => $serviceConfiguration) {
         if(isset($serviceConfiguration['disabled']) && $serviceConfiguration['disabled']) {
           $getter = $this->getServiceGetterMethod($serviceName);
           $getter->setCode("
             throw new \Frosting\IService\DependencyInjection\ServiceDisabledException('The service named [$serviceName] is disabled.');
           ");
           continue;
         }
         
         if(!isset($serviceConfiguration['class'])) {
           continue;
         }
         
         $getter = $this->getServiceGetterMethod($serviceName);
         $code = $getter->getCode() . "\n";
         $getter->setCode(
             $startCodes[$serviceName] .
             $code . "\n        return \$service;"
          ); 
      }
      
      $this->finalizeClassDefinition();
      $dumper = new \Mandango\Mondator\Dumper($this->classDefinition);
      $this->fileSystem->dumpFile($file, $dumper->dump());
    }
    
    return $className;
  }
  
  /**
   * @return \Frosting\IService\Annotation\IParsingResult
   */
  public function getCurrentParsingResult()
  {
    return $this->currentParsingResult;
  }
  
  private function mustBeRegenerated($since) {
    foreach($this->configuration as $serviceConfiguration) {
      if(!isset($serviceConfiguration['class'])) {
        continue;
      }
      
      $class = new \ReflectionClass($serviceConfiguration['class']);
      $fileName = $class->getFileName();
      if($since < filemtime($fileName)) {
        return true;
      }
    }
    $class = new \ReflectionClass($this);
    $fileName = $class->getFileName();
    return $since < filemtime($fileName);
  }

  private function finalizeClassDefinition()
  {
    $getServiceMethod = $this->getMethodByName('getServiceByName');
    $getServiceMethod->setVisibility('public');
    $getServiceMethod->setArguments('$name');
    $getServiceMethod->setCode('
    if(!isset($this->services[$name])) {
      $method = "getService_" . str_replace(".","_",$name);
      if(!method_exists($this,$method)) {
        throw new \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException(\'The service named [\' . $name . \'] does not exists.\');
      }
      $service = call_user_func(array($this,$method));
      if($service instanceof \Frosting\IService\DependencyInjection\ILifeCycleAware) {
        $service->start();
      }
    }
    
    return $this->services[$name];
    ');

    $serviceNames = array();
    foreach($this->configuration as $serviceName => $configuration) {
      if(array_key_exists('disabled', $configuration) && $configuration['disabled']) {
        continue;
      }
      $serviceNames[] = $serviceName;
    }
    
    $getServiceNamesMethod = $this->getMethodByName('getServiceNames');
    $getServiceNamesMethod->setVisibility('public');
    $getServiceNamesMethod->setCode('
      return ' . var_export($serviceNames,true) . ';
    ');
    
    $getServiceConfigurationMethod = $this->getMethodByName('getServiceConfiguration');
    $getServiceConfigurationMethod->setVisibility('public');
    $getServiceConfigurationMethod->setArguments('$name');
    $getServiceConfigurationMethod->setCode('
      $configuration = ' . var_export($this->configuration,true) . ';
      if(!array_key_exists($name,$configuration)) {
        throw new \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException(\'The service named [\' . $name . \'] does not exists.\');
      }
      
      if(!array_key_exists("configuration",$configuration[$name])) {
        return null;
      }

      return $configuration[$name]["configuration"];
    ');
    
    $shutdownMethod = $this->getMethodByName('shutdown');
    $shutdownMethod->setVisibility('public');
    $shutdownMethod->setArguments('');
    $shutdownMethod->setCode('
    foreach($this->services as $service) {
      if($service instanceof \Frosting\IService\DependencyInjection\ILifeCycleAware) {
        $service->shutdown();
      }
    }
    ');
    
    $services = new \Mandango\Mondator\Definition\Property('private','services',array());
    $this->classDefinition->addProperty($services);
    
    $tags = new \Mandango\Mondator\Definition\Property('private','tags',$this->servicesTags);
    $this->classDefinition->addProperty($tags);
    
    $getServiceByTagsMethod = $this->getMethodByName('getServicesByTag');
    $getServiceByTagsMethod->setVisibility('public');
    $getServiceByTagsMethod->setArguments('$tag');
    $getServiceByTagsMethod->setCode('
    if(!isset($this->tags[$tag])) {
      return array();
    }

    $services = array();
    foreach($this->tags[$tag] as $serviceName) {
      $services[] = $this->getServiceByName($serviceName);
    }
    
    return $services;
    ');
  }
}
