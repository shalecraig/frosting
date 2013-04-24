<?php

namespace Frosting\Cache;

class Category implements ICacheCategory
{
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var IStorage
   */
  private $storage;

  /**
   * @var ICategoryState
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
   * @param IStorage $storage
   * @param ICategoryState $systemState
   */
  public function initialize($name,$segregationPrefix, IStorage $storage, ICategoryState $systemState)
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
  
  /**
   * @core\service\Inject("event_dispatcher")
   * 
   * @param \core\event\IDispatcher $dispatcher
   */
  public function setEventDispatcher(IDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }
  
  /**
   * @return ICategory $this
   */
  public function clear()
  {
    $this->version++;
    $this->dispatcher->notify(new \core\event\Event($this,'Cache.' . $this->getName() . '.clear'));
    $this->clearDate = time();
    $this->systemState->updateData($this);
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
   * @return IStorage
   */
  public function getStorage()
  {
    return $this->storage;
  }
}