<?php

namespace Frosting\ObjectFactory\Tests;

use Frosting\IService\ObjectFactory\Tests\ObjectFactoryServiceTest;
use Frosting\ObjectFactory\ObjectFactory;

class ObjectFactoryTest extends ObjectFactoryServiceTest
{
  public function getObjectFactory() 
  {
    return new ObjectFactory();
  }
}