<?php

namespace Frosting\ObjectFactory\Tests;

use Frosting\IService\ObjectFactory\Tests\ObjectFactoryServiceTest;
use Frosting\ObjectFactory\ObjectFactory;

class ObjectFactoryTest extends ObjectFactoryServiceTest
{
  public function getObjectFactory() 
  {
    return ObjectFactory::factory();
  }
  
  public function testFactoryAnnotation()
  {
    $factory = $this->loadObjectFactory();
    $object = $factory->createObject(__NAMESPACE__ . '\TestFactoryAnnotation');
    $this->assertInstanceOf(__NAMESPACE__ . '\TestFactoryAnnotation', $object);
    $this->assertTrue($object->callByFactory);
  }
}

class TestFactoryAnnotation
{
  public $callByFactory;
  
  /**
   * @Factory
   */
  public static function factory()
  {
    $object = new static();
    $object->callByFactory = true;
    return $object;
  }
}