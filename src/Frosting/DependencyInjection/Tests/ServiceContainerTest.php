<?php

namespace Frosting\DependencyInjection\Tests;

use Frosting\DependencyInjection\BaseServiceContainer;
use Frosting\IService\DependencyInjection\Tests\ServiceContainerTest as BaseServiceContainerTest;

class ServiceContainerTest extends BaseServiceContainerTest
{
  protected function getServiceContainer($configuration) 
  {
    return BaseServiceContainer::factory($configuration);
  }
}