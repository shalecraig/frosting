<?php

namespace Frosting\Cache\CacheEngineTest;

use Frosting\IService\Cache\Tests\CacheServiceTest;
use Frosting\DependencyInjection\ServiceContainer;
use Frosting\IService\Cache\ICacheCategory;

class CacheEngineTest extends CacheServiceTest
{
  protected function getCacheService($configuration) 
  {
    $serviceContainerConfiguration = 
    array (
      'cache' => 
      array (
        'class' => 'Frosting\Cache\CacheEngine',
      ),
      'cache_category_builder' => 
      array (
        'class' => 'Frosting\Cache\CategoryBuilder',
      ),
      'cache.storage.default' => 
      array (
        'class' => 'Frosting\Cache\File\FileStorage',
        'configuration' =>
        array (
          'baseDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid()
        )
      ),
      'cache.category.system' => 
      array (
        'class' => 'Frosting\Cache\SystemCategory',
        'configuration' => 
        array (
          'storage' => 'default',
          'autoClear' => false,
        ),
      ),
      'cache.category.default' => 
      array (
        'class' => 'Frosting\Cache\Category',
        'configuration' => 
        array (
          'storage' => 'default',
          'autoClear' => true,
        ),
      ),
    );
    $categories = array_diff(
      $configuration['categories'],
      array(ICacheCategory::NAME_DEFAULT, ICacheCategory::NAME_SYSTEM)
    );
    foreach($categories as $categoryName) {
      $serviceContainerConfiguration["cache.category." . $categoryName] = 
      array (
        'class' => 'Frosting\Cache\Category',
        'builders' => 
        array (
          0 => 'cache_category_factory',
        ),
        'configuration' => 
        array (
          'storage' => 'default',
          'autoClear' => true,
        ),
      );
    }
    
    $serviceContainer = ServiceContainer::factory($serviceContainerConfiguration);
    
    return $serviceContainer->getServiceByName("cache");
  }
  
  public function setfUp() {
    $this->markTestSkipped();
  }
}