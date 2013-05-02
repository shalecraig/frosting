<?php

namespace Frosting\EventDispatcher\Tests;

use Frosting\IService\EventDispatcher\Tests\EventDispatcherServiceTest;
use Frosting\EventDispatcher\EventDispatcher;

class EventDispatcherTest extends EventDispatcherServiceTest
{
  protected function getEventDispatcherService() 
  {
    return new EventDispatcher();
  }
}