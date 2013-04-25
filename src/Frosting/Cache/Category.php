<?php

namespace Frosting\Cache;

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
   * @var IDispatcher
   */
  private $dispatcher;
  
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
  
  public function getSegregationPrefix()
  {
    return $this->segregationPrefix;
  }
  
  public function setEventDispatcher(IDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
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
   // $this->dispatcher->notify(new \core\event\Event($this,'Cache.' . $this->getName() . '.clear'));
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