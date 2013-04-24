<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Cache\Disabled;

use \Frosting\Cache\ICacheStorage;
use \Frosting\Cache\ValueNotFoundException;
use \Frosting\Cache\ICacheCategory;
use \Frosting\Cache\EntryContent;

/**
 * Storage use when we want to disable a specific category.
 * 
 * This will always throw a ValueNotFoundException
 *
 * @author mpoirier
 */
class DisableStorage implements ICacheStorage
{
  /**
   * Stores an entry content
   * @param string $entryName
   * @param EntryContent $content
   * @param ICategory $category
   * @return IStorage $this
   */
  public function store($entryName, EntryContent $content, ICacheCategory $category) {}
  
  /**
   * Throws a ValueNotFoundException if, well, it's not in the storage
   * @param string $entryName
   * @param ICategory $category
   * @return EntryContent
   */
  public function retrieve($entryName, ICacheCategory $category)
  {
    throw new ValueNotFoundException(sprintf('Category disabled %s/%s', $entryName, $category->getName()));
  }
  
  public function clear(ICacheCategory $category) {}
    
  /**
   * Remove an entry
   * @param string $entryName
   * @param ICategory $category
   * @return IStorage $this
   */
  public function remove($entryName, ICacheCategory $category) {}
}

