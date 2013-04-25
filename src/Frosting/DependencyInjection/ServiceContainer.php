<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\IService\DependencyInjection\ServiceDoesNotExistsException;
use Frosting\IService\DependencyInjection\ServiceDisabledException;
use Frosting\IService\Annotation\IAnnotationParserService;
use Frosting\IService\DependencyInjection\Tag;
use Frosting\IService\ObjectFactory\IObjectFactoryService;
use Frosting\Annotation\AnnotationParser;
use Frosting\ObjectFactory\ObjectFactory;

/**
 * Description of ServiceContainer
 *
 * @author Martin
 */
class ServiceContainer implements IServiceContainer
{
  private $services = array();
  
  private $serviceTags;
  
  private $configuration;
  
  public function __construct(array $configuration) 
  {
    $this->configuration = $configuration;
    $this->services['serviceContainer'] = $this;
  }
  
  public function setAnnotationParser(IAnnotationParserService $annotationParser)
  {
    $this->services['annotationParser'] = $annotationParser;
  }
  
  public function setObjectFactory(IObjectFactoryService $objectFactory)
  {
    $this->services['objectFactory'] = $objectFactory;
    $objectFactory->registerObjectBuilder(
      new InjecterObjectBuilder($this)
    );
    
    $tags = $this->loadServicesTag();
    if(array_key_exists('objectFactory.builder', $tags)) {
      foreach($tags['objectFactory.builder'] as $serviceName) {
        $objectFactory->registerObjectBuilder($this->getServiceByName($serviceName));
      }
    }
  }
  
  public function getServiceNames() 
  {
    return array_keys(
      array_filter($this->configuration, function($configuration) {
        return !(array_key_exists('disabled',$configuration) && $configuration['disabled']);
      })
    );  
  }
  
  public function getServiceByName($name)
  {
    if(!array_key_exists($name, $this->services)) {
      if(!array_key_exists($name, $this->configuration)) {
        throw new ServiceDoesNotExistsException('The service named [' . $name . '] does not exists.');
      }
      
      if(array_key_exists('disabled',$this->configuration[$name]) && $this->configuration[$name]['disabled']) {
        throw new ServiceDisabledException('The service named [' . $name . '] is disabled.');
      }
      
      $configuration = $this->getServiceConfiguration($name);
      $class = $this->configuration[$name]['class'];
      $this->services[$name] = $this->getObjectFactory()
        ->createObject($class,array(),array('serviceName'=>$name,'configuration'=>$configuration));
    }
    
    return $this->services[$name];
  }
  
  private function loadServicesTag()
  {
    if(is_null($this->serviceTags)) {
      $this->serviceTags = array();
      $annotationParser = $this->getAnnotationParser();
      foreach($this->getServiceNames() as $name) {
        $serviceClass = $this->configuration[$name]['class'];
        $result = $annotationParser->parse($serviceClass);
        $annotations = $result->getClassAnnotations(array(function($annotation) {return $annotation instanceof Tag;}));
        
        foreach($annotations as $annotation) {
          $this->serviceTags[$annotation->getTagName()][] = $name;
        }
      }
    }
    
    return $this->serviceTags;
  }
  
  public function getServicesByTag($tag)
  {
    $serviceTags = $this->loadServicesTag();
    
    if(!array_key_exists($tag, $serviceTags)) {
      return array();
    }
    
    $result = array();
    
    foreach($serviceTags[$tag] as $serviceName) {
      $result[] = $this->getServiceByName($serviceName);
    }
    
    return $result;
  }
  
  /**
   * @return \Frosting\IService\Annotation\IAnnotationParserService
   */
  private function getAnnotationParser()
  {
    return $this->getServiceByName('annotationParser');
  }
  
  /**
   * 
   * @return \Frosting\IService\ObjectFactory\IObjectFactoryService
   */
  private function getObjectFactory()
  {
    return $this->getServiceByName('objectFactory');
  }
  
  public function getServiceConfiguration($name) 
  {
    if(!array_key_exists($name, $this->configuration)) {
      throw new ServiceDoesNotExistsException('The service named [' . $name . '] does not exists.');
    }
    
    if(array_key_exists('configuration', $this->configuration[$name])) {
      return $this->configuration[$name]['configuration'];
    }
    
    return null;
  }
  
  public static function factory($configuration)
  {
    $serviceContainer = new static($configuration);
    $serviceContainer->setAnnotationParser(new AnnotationParser());
    $serviceContainer->setObjectFactory(new ObjectFactory());
    return $serviceContainer;
  }
}
