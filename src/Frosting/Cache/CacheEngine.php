<?php

namespace Frosting\Cache;


use Frosting\IService\Cache\ICacheService;
use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\IService\Cache\CategoryDoesNotExistsException;
use Frosting\IService\DependencyInjection\ServiceDoesNotExistsException;
use Frosting\Framework\Frosting;

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
   * @Inject
   * @param \Frosting\IService\DependencyInjection\IServiceContainer $serviceContainer
   */
  public function setServiceContainer(IServiceContainer $serviceContainer)
  {
    $this->serviceContainer = $serviceContainer;
  }
  
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
    
    $categoryServices = array_filter($serviceNames, function($serviceName) {
      return strpos($serviceName, 'cache.category.') === 0;
    });
    
    array_walk(
      $categoryServices,
      function(&$serviceName) { 
        list(,,$serviceName) = explode('.',$serviceName);
      }
    );
    
    //That way we restart the index at 0
    return array_values($categoryServices);
  }
  
  /**
   * Instantiate a category
   * @return ICategory
   */
  public function getCategory($name)
  {
    try {
      return $this->serviceContainer->getServiceByName('cache.category.' . $name);
    } catch (ServiceDoesNotExistsException $e) {
      throw new CategoryDoesNotExistsException('The category named [' . $name .'] does not exists.',null,$e);
    }
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
  
  /**
   * @param string $configuration
   * @return ICacheService
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,self::FROSTING_SERVICE_NAME);
  }
}