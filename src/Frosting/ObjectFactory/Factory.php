<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use Frosting\ObjectFactory\IAspectLikeAnnotation;
use Frosting\ObjectFactory\ChildClassDefinition;
use Mandango\Mondator\Definition\Method;


/**
 * Description of Inject
 *
 * @Annotation
 */
class Factory implements IAspectLikeAnnotation
{
  /**
   * @param \Mandango\Mondator\Definition\Method $definition
   */
  public function modifyCode(ChildClassDefinition $classDefinition, Method $methodDefinition = null)
  {
    $method = new Method('public','__selfFactory','','');
    $method->setStatic(true);
    $method->setCode('
      return call_user_func_array(array(__CLASS__,"' . $methodDefinition->getName() . '"),func_get_args());
    ');
    $classDefinition->addMethod($method);
  }
}