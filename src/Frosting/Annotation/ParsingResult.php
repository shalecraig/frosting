<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Annotation;

use \Frosting\IService\Annotation\IParsingResult;

/**
 * Description of ParsingResult
 *
 * @author mpoirier
 */
class ParsingResult implements IParsingResult
{
  private $className = null;
  
  private $classAnnotations = array();
  
  private $methodAnnotations = array();
  
  private $attributeAnnotations = array();
  
  private $haveAnnotations = false;
  
  public function __construct($className)
  {
    $this->className = $className;
  }
  
  public function getParsedClassName()
  {
    return $this->className;
  }
  
  public function getClassAnnotations(array $filters = array())
  {
    return $this->filters($this->classAnnotations, $filters);
  } 
  
  public function getAllMethodAnnotations(array $filters = array()) 
  {
    $result = array();
    foreach($this->methodAnnotations as $methodName => $annotations) {
      $result[$methodName] = $this->filters($annotations, $filters);
    }
    
    return $result;
  }
  
  public function getMethodAnnotations($name,array $filters = array())
  {
    if(!array_key_exists($name, $this->methodAnnotations)) {
      return array();
    }
    
    return $this->filters($this->methodAnnotations[$name], $filters);
  }
  
  public function getAllAttributeAnnotations(array $filters = array()) 
  {
    $result = array();
    foreach($this->attributeAnnotations as $attributeName => $annotations) {
      $result[$attributeName] = $this->filters($annotations, $filters);
    }
    
    return $result;
  }
  
  public function getAttributeAnnotations($name, array $filters = array())
  {
    if(!array_key_exists($name, $this->attributeAnnotations)) {
      return array();
    }
    
    return $this->filters($this->attributeAnnotations[$name], $filters);
  }
  
  private function filters($annotations,$filters) 
  {
    foreach($filters as $filter) {
      $annotations = array_filter($annotations,$filter);
    }
    return $annotations;
  }
  
  public function setClassAnnotations($annotations)
  {
    $this->classAnnotations = $annotations;
    $this->haveAnnotations = $this->haveAnnotations || count($annotations) > 0;
  }
  
  public function setMethodAnnotation($methodName,$annotations)
  {
    $this->methodAnnotations[$methodName] = $annotations;
    $this->haveAnnotations = $this->haveAnnotations || count($annotations) > 0;
  }

  public function haveAnnotations()
  {
    return $this->haveAnnotations;
  }
  
  public function isAnnotedWith($annotationClass, $annotationFilterCallback = null)
  {
    if(is_null($annotationFilterCallback)) {
      $annotationFilterCallback = function($annotation) { return true ;};
    }
    
    foreach($this->getMethodAnnotations() as $annotations) {
      foreach($annotations as $annotation) {
        if($annotation instanceof $annotationClass && $annotationFilterCallback($annotation)) {
          return true;
        }
      }
    }
    
    foreach($this->classAnnotations as $annotation) {
      if($annotation instanceof $annotationClass && $annotationFilterCallback($annotation)) {
        return true;
      }
    }
    
    return false;
  }
  
  public function mergeParentClass(ParsingResult $parentResult)
  {    
    $this->methodAnnotations = array_merge($parentResult->methodAnnotations,$this->methodAnnotations); 
    $this->classAnnotations = array_merge($parentResult->classAnnotations,$this->classAnnotations);
    $this->haveAnnotations = $this->haveAnnotations || $parentResult->haveAnnotations;
  }
  
}
