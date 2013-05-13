<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Cache;

use Frosting\IService\ObjectFactory\IObjectBuilder;
use Frosting\IService\Cache\ICacheService;

/**
 * Description of CategoryFactory
 *
 * @author Martin
 * 
 * @Tag("objectFactory.builder")
 */
class CategoryBuilder implements IObjectBuilder
{
  /**
   * @var CacheEngine
   */
  private $cacheEngine;

  /**
   * @param \Frosting\IService\Cache\ICacheService $cache
   * 
   * @Inject
   */
  public function setCacheEngine(ICacheService $cache)
  {
    $this->cacheEngine = $cache;
  }
  
  public function initializeObject($service,array $contextParameters = array()) 
  {
    if(!($service instanceof Category)) {
      return;
    }
    $this->build($service,$contextParameters['serviceName'],$contextParameters['configuration']);
  }
  
  public function build($service, $serviceName, $configuration) 
  {
    if($service instanceof Category) {
      list(,, $categoryName) = explode('.', $serviceName);

      $configuration = array_merge(
        array('storage' => 'default', 'segregationKeys' => array()), 
        $configuration
      );

      $storageName = $configuration['storage'];

      $segregationPrefix = $this->getSegrationPrefix($configuration['segregationKeys']);

      $state = new CategoryState(
        $this->cacheEngine->entryFactory(
          $segregationPrefix . $categoryName, ICacheCategory::NAME_SYSTEM
        )
      );

      $service->initialize(
        $categoryName, 
        $segregationPrefix,
        $this->cacheEngine->getStorage($storageName), 
        $state
      );
      return;
    }
    
    if($service instanceof SystemCategory) {
      $service->initialize($this->cacheEngine->getStorage($configuration['storage']));
      return;
    }
  }

  private function getSegrationPrefix($segregationKeys) {
    return implode(
      '_', 
      array_intersect_key(
        array(), 
        array_flip($segregationKeys)
      )
    );
  }
}
