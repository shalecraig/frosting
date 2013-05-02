<?php

namespace Frosting\Cache;

use \Frosting\IService\EventDispatcher\IEventDispatcherService;

class Category implements ICacheCategory
{
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var ICacheStorage
   */
  private $storage;

  /**
   * @var CategoryState
   */
  private $systemState;

  /**
   * @var integer
   */
  private $version;

  /**
   * @var integer Timestamp
   */
  private $clearDate;

  /**
   *
   * @var \Frosting\IService\EventDispatcher\IEventDispatcherService
   */
  private $eventDispatcher;
  
  /**
   * @param string $name
   * @param ICacheStorage $storage
   * @param CategoryState $systemState
   */
  public function initialize($name,$segregationPrefix, ICacheStorage $storage, CategoryState $systemState)
  {
    $this->name = $name;
    $this->storage = $storage;
    $this->systemState = $systemState;
    $this->version = $this->systemState->getVersion();
    $this->clearDate = $this->systemState->getCreatedAt();
    $this->segregationPrefix = $segregationPrefix;
  }
  
  /**
   * @param \Frosting\IService\EventDispatcher\IEventDispatcherService $eventDispatcher
   * 
   * @Inject
   */
  public function setEventDispatcher(IEventDispatcherService $eventDispatcher)
  {
    $this->eventDispatcher = $eventDispatcher;
  }
  
  public function getSegregationPrefix()
  {
    return $this->segregationPrefix;
  }
  
  /**
   * @return ICategory $this
   */
  public function clear()
  {
    //We do a clone to be able to keep the old value and call clear on storage
    //after it have been clear and warmup is completed
    $category = clone $this;
    $this->version++;
    $this->eventDispatcher->dispatch('Cache.' . $this->getName() . '.clear', $this);
    $this->clearDate = time();
    $this->systemState->updateData($this);
    
    $this->storage->clear($category);
    return $this;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * The id where values are readable
   * @return integer
   */
  public function getVersion()
  {
    return $this->version;
  }
  
  /**
   * The date it was cleared
   * @param string $format
   * @return string
   */
  public function getVersionCreationTimestamp()
  {
    return $this->clearDate;
  }
  
  /**
   * @return ICacheStorage
   */
  public function getStorage()
  {
    return $this->storage;
  }
}