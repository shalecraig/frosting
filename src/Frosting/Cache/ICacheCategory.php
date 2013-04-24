<?php

namespace Frosting\Cache;

/**
 * Interface for categories
 */
interface ICacheCategory extends \Frosting\IService\Cache\ICacheCategory
{
  public function getSegregationPrefix();
  
  /**
   * @return ICacheStorage
   */
  public function getStorage();
}