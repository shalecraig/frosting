<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Cache;

/**
 * Description of CategoryFactory
 *
 * @author Martin
 */
class CategoryBuilder
{
  /**
   * @var CacheEngine
   */
  private $cacheEngine;

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
        $this->segregationKeys, 
        array_flip($segregationKeys)
      )
    );
  }
}
