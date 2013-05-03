<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\DependencyInjection\Inject as BaseInject;
use Frosting\ObjectFactory\IAspectLikeAnnotation;
use Frosting\ObjectFactory\ChildClassDefinition;
use Mandango\Mondator\Definition\Method;

/**
 * @Annotation
 */
class Inject extends BaseInject implements IAspectLikeAnnotation
{
  /**
   * @param \Mandango\Mondator\Definition\Method $definition
   */
  public function modifyCode(ChildClassDefinition $classDefinition, Method $methodDefinition = null)
  {
    $initializationMethod = $classDefinition->getInitililizationMethod();
    $code = $initializationMethod->getCode();
    
    $parameters = $this->getParameters($classDefinition->getParentClass(),$methodDefinition->getName());
    
    $code .= "\n" . '$object->' . $methodDefinition->getName() . '(' . implode("\n,", $parameters) . ');';

    $initializationMethod->setCode($code);
  }
  
  private function getParameters($class, $method)
  {
    $reflectionMethod = new \ReflectionMethod($class, $method);
    
    $mapping = $this->getMapping();
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
          $parameters[$parameter->getPosition()] = '$serviceContainer->getServicesByTag("' . substr($serviceName,1) . '")';
          break;
        case strpos($serviceName, '$') === 0:
          if($serviceName == '$') {
            $parameters[$parameter->getPosition()] = 'isset($contextParameters["configuration"]) ? $contextParameters["configuration"] : null';
          } else {
            $parameters[$parameter->getPosition()] = '$serviceContainer->getServiceByName("configuration")->get("' . substr($serviceName,1) . '")';
          }
          
          break;
        default:
          $parameters[$parameter->getPosition()] = '$serviceContainer->getServiceByName("' . $serviceName . '")';
          break;
      }
    }
    return $parameters;
  }
}
