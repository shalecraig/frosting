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
   * @var IStorage
   */
  private $storage;

  
  /**
   * @return ICategory $this
   */
  public function clear() {}
  
  /**
   * @param IStorage $storage
   */
  public function initialize(IStorage $storage)
  {
    $this->storage = $storage;
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return ICategory::NAME_SYSTEM;
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
   * @return IStorage
   */
  public function getStorage()
  {
    return $this->storage;
  }
}