<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Annotation;

use \Doctrine\Common\Annotations\AnnotationRegistry;
use \Doctrine\Common\Annotations\AnnotationReader;
use \Frosting\IService\Annotation\IAnnotationParserService;

/**
 * Description of Parser
 *
 * @author mpoirier
 */
class AnnotationParser implements IAnnotationParserService
{
  /**
   * @var \Doctrine\Common\Annotations\AnnotationReader
   */
  private $reader = null;
  
  public function __construct()
  {
    $this->reader = new AnnotationReader();
    AnnotationRegistry::registerLoader(function($class) {
      return class_exists($class,true);
    });
  }
  
  /**
   * @param type $class
   * @return \Frosting\IService\Annotation\IParsingResult 
   */
  public function parse($className)
  {
    $reflectionClass = new \ReflectionClass($className);
    $result = new ParsingResult($reflectionClass->getName());
    
    $result->setClassAnnotations($this->reader->getClassAnnotations($reflectionClass));
    foreach($reflectionClass->getMethods() as $reflectionMethod) {      
      $result->setMethodAnnotations(
        $reflectionMethod->getName(),
        $this->reader->getMethodAnnotations($reflectionMethod)
      );
    }
    
    foreach($reflectionClass->getMethods() as $reflectionMethod) {      
      $result->setMethodAnnotations(
        $reflectionMethod->getName(),
        $this->reader->getMethodAnnotations($reflectionMethod)
      );
    }
    
    foreach($reflectionClass->getProperties() as $reflectionProperty) {      
      $result->setPropertyAnnotations(
        $reflectionProperty->getName(),
        $this->reader->getPropertyAnnotations($reflectionProperty)
      );
    }
    
    $parentClass = $reflectionClass->getParentClass();
    if($parentClass) {
      $parentResult = $this->parse($parentClass->getName());
      $result->mergeParentClass($parentResult);
    }
    
    $interfaceClasses = $reflectionClass->getInterfaces();
    foreach($interfaceClasses as $interfaceClass)
    {
      /* @var $interfaceClass \ReflectionClass  */
      $interfaceResult = $this->parse($interfaceClass->getName());
      $result->mergeParentClass($interfaceResult);
    }
    
    return $result;
  }
}
