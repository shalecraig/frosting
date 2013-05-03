<?php

namespace Frosting\DependencyInjection\Tests;

use Frosting\DependencyInjection\ServiceContainer;
use Frosting\IService\DependencyInjection\Tests\ServiceContainerTest as BaseServiceContainerTest;

class ServiceContainerTest extends BaseServiceContainerTest
{
  protected function getServiceContainer($configuration) 
  {
    return ServiceContainer::factory(array('services'=>$configuration));
  }
}