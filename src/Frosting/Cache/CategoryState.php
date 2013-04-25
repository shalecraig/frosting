<?php

namespace Frosting\Cache;

use \Frosting\IService\Cache\ValueNotFoundException;

/**
 * Contains information about the system, and helps manage that information
 */
class CategoryState
{
  /**
   *
   * @var Entry
   */
  private $entry = null;
  
  public function __construct(Entry $entry)
  {
    $this->entry = $entry;
    
    try {
      $data = $entry->get();
    } catch (ValueNotFoundException $e) {
      $data = array('version'=>1,'createdAt'=>time());
      $entry->set($data);
    }

    if(!is_array($data)) {
      $data = array();
    }
    
    $data = array_merge(
      array('version'=>1,'createdAt'=>time()),
      $data
    );

    $this->data = $data;
  }
  
  /**
   * This is usually called just before and after a category's clearing process
   * @param ICacheCategory $category
   */
  public function updateData(ICacheCategory $category)
  {
    $this->data['createdAt'] = $category->getVersionCreationTimestamp();
    $this->data['version'] = $category->getVersion();
    $this->entry->set($this->data);
  }
  
  /**
   * @param string $name
   * @return integer
   */
  public function getCreatedAt()
  {
    return $this->data['createdAt'];
  }
    
  /**
   * @param string $name
   * @return integer
   */
  public function getVersion()
  {
    return $this->data['version'];
  }
}