<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use \Frosting\IService\ObjectFactory\IObjectBuilder;
use \Frosting\IService\DependencyInjection\Inject as BaseInject;
use \Frosting\IService\DependencyInjection\IServiceContainer;

/**
 * Description of InjecterObjectBuilder
 *
 * @author Martin
 */
class InjecterObjectBuilder implements IObjectBuilder
{
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private $serviceContainer;
  
  public function __construct(IServiceContainer $serviceContainer) 
  {
    $this->serviceContainer = $serviceContainer;
  }
  
  public function initializeObject($mixed, array $contextParameters = array())
  {
    $result = $this->getAnnotationParser()->parse(get_class($mixed));
    $methodAnnotations = $result->getAllMethodAnnotations(
      array(function($annotation) {return $annotation instanceof BaseInject;})
    );
      
    foreach($methodAnnotations as $methodName => $annotations) {
      foreach($annotations as $annotation) {
        $this->inject($mixed, $methodName, $annotation, $contextParameters);
      }
    }  
  }
  
  /**
   * @return \Frosting\IService\Annotation\IAnnotationParserService;
   */
  private function getAnnotationParser()
  {
    return $this->serviceContainer->getServiceByName("annotationParser");
  }
  
  private function inject($object,$method, Inject $annotation, $contextParameters)
  {
    if(array_key_exists('serviceName', $contextParameters)) {
      $currentServiceName = $contextParameters['serviceName'];
    } else {
      $currentServiceName = null;
    }
    
    $reflectionMethod = new \ReflectionMethod(get_class($object), $method);
    
    $mapping = $annotation->getMapping();
    $parameters = array();
    foreach($reflectionMethod->getParameters() as $parameter) {
      /* @var  $parameter \ReflectionParameter */
      if(array_key_exists($parameter->getName(), $mapping)) {
        $serviceName = $mapping[$parameter->getName()];
      } else {
        $serviceName = $parameter->getName();
      }
      
      switch(true) {
        case strpos($serviceName, '@') === 0:
          $parameters[$parameter->getPosition()] = $this->serviceContainer->getServicesByTag(substr($serviceName,1));
          break;
        case strpos($serviceName, '$') === 0:
          if($serviceName == '$') {
            $configurationService = $currentServiceName;
          } else {
            $configurationService = substr($serviceName,1);
          }
          $parameters[$parameter->getPosition()] = $this->serviceContainer->getServiceConfiguration($configurationService);
          break;
        default:
          $parameters[$parameter->getPosition()] = $this->serviceContainer->getServiceByName($serviceName);
          break;
      }
    }
    
    $reflectionMethod->invokeArgs($object,$parameters);
  }
}
