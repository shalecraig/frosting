<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\Annotation;

/**
 * Description of IParsingResult
 *
 * @author Martin
 */
interface IParsingResult 
{
  public function getParsedClassName();
  
  public function getClassAnnotations(array $filters = array());
  
  public function getAllMethodAnnotations(array $filters = array());
  
  public function getMethodAnnotations($name,array $filters = array());
  
  public function getAllAttributeAnnotations(array $filters = array());
  
  public function getAttributeAnnotations($name,array $filters = array());
}
