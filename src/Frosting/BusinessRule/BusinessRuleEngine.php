<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\BusinessRule;

use Frosting\IService\Invoker\IInvokerService;
use Frosting\IService\ObjectFactory\IObjectFactoryService;
use Frosting\Framework\Frosting;
use ArrayObject;
use Symfony\Component\Yaml\Yaml;


/**
 * Description of BussinessRuleEngine
 *
 * @author Martin
 */
class BusinessRuleEngine 
{
  private $rulesObject = array();
  
  private $rulesClass = array();
  
  private $defaultRule = array();
  
  /**
   * @var \Symfony\Component\Yaml\Yaml
   */
  private $yamlParser;
  
  /**
   * @var \Frosting\IService\Invoker\IInvokerService
   */
  private $invoker;
  
  /**
   * @var \Frosting\IService\ObjectFactory\IObjectFactoryService 
   */
  private $objectFactory;
  
  /** 
   * @param \Frosting\IService\ObjectFactory\IObjectFactoryService $objectFactory
   * @param \Frosting\IService\Invoker\IInvokerService $invoker
   * @param \Symfony\Component\Yaml\Yaml $yamlParser
   * 
   * @Inject
   */
  public function initialize(
    IObjectFactoryService $objectFactory, 
    IInvokerService $invoker,
    Yaml $yamlParser
  )
  {
    $this->objectFactory = $objectFactory;
    $this->invoker = $invoker;
    $this->yamlParser = $yamlParser;
  }
  
  public function setRule($rule, $class)
  {
    $this->rulesClass[$rule] = $class;
  }
  
  public function setDefaultRule($context, $ruleName, $parameterName)
  {
    $this->defaultRule[$context] = array(
      'rule'=>$ruleName,
      'parameter'=>$parameterName
    );
  }
  
  public function getFirstMatch($rules, $context = "default",array $contextParameters = array()) 
  {
    foreach($rules as $index => $ruleComposition) {
      if($this->check($ruleComposition,  $context, $contextParameters)) {
        return $index;
      }
    }
    
    return null;
  }
  
  public function getAllMatches($rules, $context = "default",array $contextParameters = array()) 
  {
    $result = array();
    foreach($rules as $index => $ruleComposition) {
      if($this->check($ruleComposition,  $context, $contextParameters)) {
        $result[] = $index;
      }
    }
    
    return $result;
  }
  
  public function check($ruleComposition, $context = "default", array $contextParameters = array())
  {
    $engine = $this;
    $callback = function($rule) use ($engine, $contextParameters, $context) {
      list($ruleName, $ruleParameters) = $engine->extractRuleParameters($rule);
      $ruleParameters = new ArrayObject($ruleParameters);
      $ruleObject = $this->getRule($ruleName, $context, $ruleParameters);
      return $this->invoker->invoke($ruleObject,$ruleParameters->getArrayCopy(),$contextParameters);
    };
    
    return $this->enforce($ruleComposition, $callback);
  }
  
  private function getRule($ruleName, $context, ArrayObject $parameters)
  {
    $ruleContext = null;
    if(strpos($ruleName, '\\') !== false) {
      list($ruleContext,$ruleName) = explode('\\',$ruleName);
    }
    
    if(is_null($ruleContext)) {
      $ruleContext = $context;
      if(!isset($this->rulesClass[$ruleContext . '\\' . $ruleName])) {
        $defaultRuleConfiguration = $this->getDefaultRuleName($context);
        if($defaultRuleConfiguration['parameter']) {
          $parameters[$defaultRuleConfiguration['parameter']] = $ruleName;
        }
        $ruleName = $defaultRuleConfiguration['rule'];
      }
    }
    
    if(!isset($this->rulesObject[$ruleContext . '\\' . $ruleName])) {
      $class = $this->rulesClass[$ruleContext . '\\' . $ruleName];
      $this->rulesObject[$ruleContext . '\\' . $ruleName] = $this->objectFactory->createObject($class);
    }
    
    return $this->rulesObject[$ruleContext . '\\' . $ruleName];
  }
  
  private function getDefaultRuleName($context)
  {
    if(array_key_exists($context, $this->defaultRule)) {
      return $this->defaultRule[$context];
    }
    
    return array('rule'=>null,'parameter'=>null);
  }
  
  private function extractRuleParameters($rule)
  {
    if(false !== $pos = strpos($rule, '{')) {
      list($rule, $parameterString) = explode('{', $rule, 2);
      $parameters = $this->yamlParser->parse('{' . $parameterString,true);
    } else {
      $parameters = array();
    }
    return array($rule,$parameters);
  }
  
  private function enforce($checks, $callback, $useAnd = true)
  {
    if (!is_array($checks)) {
      $not = false;
      if ($checks{0} == '!') {
        $not = true;
        $checks = substr($checks, 1);
      }
      $result = $callback($checks);
      return $not ? !$result : $result;
    }

    $test = true;
    foreach ($checks as $rule) {
      // recursively check the rule with a switched AND/OR mode
      $test = $this->enforce($rule,$callback,  $useAnd ? false : true);
      if (!$useAnd && $test) {
        return true;
      }

      if ($useAnd && !$test) {
        return false;
      }
    }
    return $test;
  }
  
  /**
   * @param mixed $configuration
   * @return BusinessRuleEngine
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,'businessRuleEnforcer');
  }
}
