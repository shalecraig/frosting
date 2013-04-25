<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\ObjectFactory;

use Frosting\IService\ObjectFactory\IObjectFactoryService;
use \Frosting\IService\ObjectFactory\IObjectBuilder;

/**
 * Description of ObjecFactory
 *
 * @author Martin
 */
class ObjectFactory implements IObjectFactoryService
{
  /**
   * @var \Frosting\IService\ObjectFactory\IObjectBuilder[]
   */
  private $builders = array();
  
  /**
   * @return mixed 
   */
  public function createObject($class,array $constructorArguments = array(), $contextParameters = array())
  {
    $reflectionClass = new \ReflectionClass($class);
    $object = $reflectionClass->newInstanceArgs($constructorArguments);
    foreach($this->builders as $builder) {
      $builder->initializeObject($object, $contextParameters);
    }
    return $object;
  }
  
  /**
   * @param \Frosting\IService\ObjectFactory\IObjectBuilder $objectBuilder
   */
  public function registerObjectBuilder(IObjectBuilder $objectBuilder)
  {
    $this->builders[] = $objectBuilder;
  }
}
