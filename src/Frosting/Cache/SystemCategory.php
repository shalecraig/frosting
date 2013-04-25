<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Cache;

/**
 * Description of SystemCategory
 *
 * @author Martin
 */
/**
 * The special system category
 */
class SystemCategory implements ICacheCategory
{
  /**
   * @var ICacheStorage
   */
  private $storage;

  
  /**
   * @return ICategory $this
   */
  public function clear() {}
  
  /**
   * @param ICacheStorage $storage
   */
  public function initialize(ICacheStorage $storage)
  {
    $this->storage = $storage;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return ICacheCategory::NAME_SYSTEM;
  }
  
  /**
   * The id where values are readable
   * @return integer
   */
  public function getVersion()
  {
    return 1;
  }
  
  /**
   * The date it was cleared
   * @param string $format
   * @return string
   */
  public function getVersionCreationTimestamp()
  {
    return 0;
  }
  
  /**
   * @return ICacheStorage
   */
  public function getStorage()
  {
    return $this->storage;
  }
  
  public function getSegregationPrefix() 
  {
    return '';
  }
}