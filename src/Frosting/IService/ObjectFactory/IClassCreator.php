<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\ObjectFactory;

use Frosting\ObjectFactory\ChildClassDefinition;

/**
 * Description of IBuilder
 *
 * @author Martin
 */
interface IClassCreator 
{
  public function modifyCode(ChildClassDefinition $childClassDefinition);
}
