<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use \Frosting\IService\DependencyInjection\IServiceContainer;
use \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException;
use \Frosting\IService\DependencyInjection\ServiceDisabledException;
use \Frosting\IService\Annotation\IAnnotationParserService;
use \Frosting\IService\DependencyInjection\Tag;

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
  }
  
  public function setAnnotationParser(IAnnotationParserService $annotationParser)
  {
    $this->services['annotation_parser'] = $annotationParser;
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
      $class = $this->configuration[$name]['class'];
      $this->services[$name] = new $class();
    }
    
    return $this->services[$name];
  }
  
  public function getServicesByTag($tag)
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
    
    if(!array_key_exists($tag, $this->serviceTags)) {
      return array();
    }
    
    $result = array();
    
    foreach($this->serviceTags[$tag] as $serviceName) {
      $result[] = $this->getServiceByName($serviceName);
    }
    
    return $result;
  }
  
  /**
   * @return \Frosting\IService\Annotation\IAnnotationParserService
   */
  private function getAnnotationParser()
  {
    return $this->getServiceByName('annotation_parser');
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
}
