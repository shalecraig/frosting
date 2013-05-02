<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use \Mandango\Mondator\Definition\Method;

/**
 *
 * @author Martin
 */
interface IAspectLikeAnnotation {
  public function modifyCode(ChildClassDefinition $classDefinition, Method $methodDefinition = null);
}
