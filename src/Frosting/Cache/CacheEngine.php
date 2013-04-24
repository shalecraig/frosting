<?php

namespace Frosting\Cache;


use \Frosting\IService\Cache\ICacheService;

/**
 * The key master: used by clients to access the key-value caching system
 */
class CacheEngine implements ICacheService
{
  /**
   * The segregation keys: will allow categories to be segregated by env for
   * example
   * @var array
   */
  private $segregationKeys;
  
  /**
   *
   * @var \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private $serviceContainer;
  
  /**
   * The main method used by clients, gives access to cache entries
   * @param string $name
   * @param string $categoryName
   * @return Entry
   */
  public function entryFactory($name, $categoryName = ICategory::NAME_DEFAULT)
  {
    return new Entry($name, $this->getCategory($categoryName));
  }
  
  public function getAllCategories() 
  {
    $categories = array();
    foreach($this->getAllCategoryNames() as $categoryName) {
      $categories[] = $this->getCategory($categoryName);
    }
    
    return $categories;
  }
  
  /**
   * @return array Array of string
   */
  public function getAllCategoryNames()
  {
    $serviceNames = $this->serviceContainer->getServiceNames();
    
    return array_filter($serviceNames, function($serviceName) {
      return strpos($serviceName, 'cache.category.') === 0;
    });
  }
  
  /**
   * Instantiate a category
   * @return ICategory
   */
  public function getCategory($name)
  {
    return $this->serviceContainer->getServiceByName('cache.category.' . $name);
  }

  /**
   * Instantiate a storage
   * @param string $name
   * @return 
   */
  public function getStorage($name)
  {
    return $this->serviceContainer->getServiceByName("cache.storage." . $name);
  }
}