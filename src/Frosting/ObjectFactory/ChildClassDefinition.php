<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use Mandango\Mondator\Definition\Definition;
use Mandango\Mondator\Definition\Method;

/**
 * Description of ChildClassDefinition
 *
 * @author Martin
 */
class ChildClassDefinition extends Definition {
  
  private $serviceContainerClosureInitialize = false;
  
  private $overridenMethods = array();
  
  private $baseMethodCodes = array();
  
  public function getInitililizationMethod()
  {
    if(!$this->hasMethodByName('__factoryInitialization')) {
      $method = new Method('public static','__factoryInitialization','$object, $serviceContainer,array $contextParameters = array()','');
      $this->addMethod($method);
    }
    
    return $this->getMethodByName('__factoryInitialization');
  }
  
  /**
   * 
   * @param string $method
   * @return \Mandango\Mondator\Definition\Method
   */
  public function getMethodOverride($methodName)
  {
    if(!array_key_exists($methodName, $this->overridenMethods)) {
      $sourceMethod = new \ReflectionMethod($this->getParentClass(),$methodName);
      switch(true) {
        case $sourceMethod->isPrivate():
          return null;
        case $sourceMethod->isProtected():
          $visibility = 'protected';
          break;
        case $sourceMethod->isPublic():
        default:
          $visibility = 'public';
          break;
      }

      $methodDefinition = new Method($visibility,$sourceMethod->getName(),'','');

      list($parameters, $argumentNames) = self::getArgumentsInformationFromReflectionParameters($sourceMethod->getParameters());
      
      $methodDefinition->setArguments(implode(', ', $parameters));
      $methodDefinition->setCode(
        '
        $result = parent::' . $sourceMethod->getName() . '(' . implode(',', $argumentNames) . ');
        '
      );
      
      $methodDefinition->setDocComment($sourceMethod->getDocComment());
       
      $this->baseMethodCodes[$methodName] = $methodDefinition->getCode();
      $this->overridenMethods[$methodName] = $methodDefinition;
      $this->addMethod($this->overridenMethods[$methodName]);
    }
    
    return $this->overridenMethods[$methodName];
  }
  
  public function getServiceObjectCode($serviceName)
  {
    if(!$this->serviceContainerClosureInitialize) {
      $this->serviceContainerClosureInitialize = true;
      $initializationMethod = $this->getInitililizationMethod();
      if(!$this->hasPropertyByName('__serviceContainerClosure')) {
        $this->addProperty(new \Mandango\Mondator\Definition\Property('private','__serviceContainerClosure',null));
        $initializationMethod->setCode($initializationMethod->getCode() . '
    $object->__serviceContainerClosure = function() use ($serviceContainer) { return $serviceContainer; };
');
      }
    }
    
    return 'call_user_func($this->__serviceContainerClosure)->getService("' . $serviceName . '")';
  }
  
  public function finalize()
  {
    foreach($this->overridenMethods as $methodName => $method) {
      if($method->getCode() == $this->baseMethodCodes[$methodName]) {
        $this->removeMethodByName($methodName);
        continue;
      }
      $method->setCode($method->getCode() . "\n    return \$result;");
    }
  }
  
  /**
   * 
   * @param \ReflectionParameter[] $parameters
   */
  public static function getArgumentsInformationFromReflectionParameters($parameters)
  {
    $arguments = array();
    $argumentNames = array();
    foreach ($parameters as $reflectionParameter) {
      $argumentNames[] = '$' . $reflectionParameter->getName();
      $argument = array();
      $parameterClass = $reflectionParameter->getClass();
      if ($parameterClass) {
        $argument[] = '\\' . $parameterClass->getName();
      }
      $argument[] = '$' . $reflectionParameter->getName();
      if ($reflectionParameter->isDefaultValueAvailable()) {
        $argument[] = '= ' . var_export($reflectionParameter->getDefaultValue(),true);
      } else if ($reflectionParameter->isOptional() && $reflectionParameter->allowsNull()) {
        $argument[] = '= null';
      } 
      $arguments[] = implode(' ', $argument);
    }
    
    return array($arguments,$argumentNames);
  }
}
