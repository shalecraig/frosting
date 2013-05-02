<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use Frosting\IService\ObjectFactory\IClassCreator;
use Frosting\IService\Annotation\IAnnotationParserService;

/**
 * Description of ClassGenerator
 *
 * @author Martin
 */
class AnnotationClassCreator implements IClassCreator
{
  private $annotationParser = null;
  
  public function __construct(IAnnotationParserService $annotationParser) 
  {
    $this->annotationParser = $annotationParser;
  }
  
  public function modifyCode(ChildClassDefinition $classDefinition)
  {
    $parsingResult = $this->annotationParser->parse($classDefinition->getParentClass());
    
    $methodAnnotations = $parsingResult->getAllMethodAnnotations(
      array(function($annotation) {return $annotation instanceof IAspectLikeAnnotation;})
    );
    
    foreach($methodAnnotations as $methodName => $annotations) {
      if(!$annotations) {
        continue;
      }
      
      $methodDefinition = $classDefinition->getMethodOverride($methodName);

      if(!$methodDefinition) {
        continue;
      }
      
      foreach($annotations as $annotation) {
        $annotation->modifyCode($classDefinition,$methodDefinition);
      }
    }
    
    return $classDefinition;
  }
}