<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\Annotation;

/**
 *
 * @author Martin
 */
interface IAnnotationParserService
{
  /**
   * The service name use as a reference
   */
  const FROSTING_SERVICE_NAME = "annotationParser";
  
  /**
   * 
   * @param type $className
   * 
   * @return \Frosting\IService\Annotation\IParsingResult
   */
  public function parse($className);
}