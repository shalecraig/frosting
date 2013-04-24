<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\ObjectFactory;

/**
 *
 * @author Martin
 */
interface IObjectFactory {
    
  /**
   * @return mixed 
   */
  public function createObject($class,array $constructorArguments = array());
}

