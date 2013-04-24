<?php

namespace Frosting\Cache;

use \Frosting\IService\Cache\ICacheEntry;
use \Frosting\IService\Cache\ValueNotFoundException;

/**
 * A cache entry, this is what clients will manipulate
 */
class Entry implements ICacheEntry
{
  /**
   * Name of the system state entry
   * @var string
   */
  const NAME_SYSTEM_STATE = 'systemstate';
  
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var mixed
   */
  private $value;
  
  /**
   * @var integer Timestamp
   */
  private $updateTimestamp;
  
  /**
   * @var integer 0 = no limit
   */
  private $lifetime;
  
  /**
   * @var ICacheCategory
   */
  private $category;
  
  /**
   * Was the entry already retrieved from cache?
   * @var boolean
   */
  private $retrieved = false;
  
  /**
   * Is this entry not in cache already?
   * @var boolean
   */
  private $newborn = true;
  
  
  /**
   * @param string $name
   * @param ICategory $category
   */
  public function __construct($name, ICacheCategory $category)
  {
    $this->name = $name;
    $this->category = $category;
  }
  
  /**
   * Get the value from the cache storage, or throw a EntryNotFoundException
   * @param boolean $forceRepoll Force repolling the storage for the content
   * @return mixed
   */
  public function get($forceRepoll = false)
  {
    if (!$this->retrieved || $forceRepoll) {
      $this->retrieved = true;
      
      //Is value in cache?
      try {
        $content = $this->category->getStorage()->retrieve(
          $this->name, 
          $this->category
        );
      } catch (ValueNotFoundException $e) {
        throw new ValueNotFoundException(
          sprintf('Storage says: %s', $e->getMessage()),
          null,
          $e
        );
      }
      
      switch(true) {
        case !($content instanceof EntryContent):
          throw new ValueNotFoundException('Return value from storage not valid');
        case $content->getUpdateTimestamp() < $this->category->getVersionCreationTimestamp():
          throw new ValueNotFoundException('Cleared');
        case $content->isExpired(time()):
          throw new ValueNotFoundException('Expired');
      }
      
      $this->newborn = false;
      $this->value = $content->getValue();
      $this->updateTimestamp = $content->getUpdateDate();
      $this->lifetime = $content->getLifetime();
    }
    
    if ($this->newborn) {
      throw new ValueNotFoundException('Not found I told you!');
    }
    
    return $this->value;
  }
  
  /**
   * Sets the value in the cache storage
   * @param mixed $value
   * @param integer $lifetime
   * @return Entry $this
   */
  public function set($value, $lifetime = 0)
  {
    $this->retrieved = true;
    $this->value = $value;
    $this->updateTimestamp = time();
    $this->lifetime = $lifetime;
    $this->newborn = false;
    
    $this->category->getStorage()->store(
      $this->name, 
      new EntryContent($this->value, $this->updateTimestamp, $this->lifetime), 
      $this->category
    );
     
    return $this;
  }

  /**
   * Deletes the value in the cache storage
   * @return Entry $this
   */
  public function delete()
  {
    $this->category->getStorage()->remove($this->name, $this->category);
    
    $this->retrieved = true;
    $this->newborn = true;
    
    return $this;
  }
  
  /**
   * 
   * @return type
   */
  public function getUpdateTimestamp()
  {
    $this->get();
    return $this->updateTimestamp;
  }
  
  /**
   * Return the last lifetime set on the entry
   * 
   * @return integer
   */
  public function getLifetime()
  {
    return $this->lifetime;
  }
  
  public function getCategory() 
  {
    return $this->category;  
  }
}