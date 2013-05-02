<?php

namespace Frosting\Cache\CacheEngineTest;

use Frosting\IService\Cache\Tests\CacheServiceTest;
use Frosting\Cache\CacheEngine;

class CacheEngineTest extends CacheServiceTest
{
  protected function getCacheService($configuration) 
  {
    return CacheEngine::factory();
  }
}