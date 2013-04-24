<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Cache;

/**
 *
 * @author Martin
 */
interface ICacheStorage 
{
  /**
   * Store the content of the entry
   * 
   * @param string $entryName
   * @param string $content
   * @param ICategory $category
   */
  public function store($entryName, $content, ICacheCategory $category);
  
  /**
   * Return the content of the entry has is or null if not found
   * 
   * @param string $entryName
   * @param ICategory $category
   * @return \Frosting\Cache\EntryContent
   */
  public function retrieve($entryName, ICacheCategory $category);
    
  /**
   * Remove an entry
   * @param string $entryName
   * @param ICategory $category
   */
  public function remove($entryName, ICacheCategory $category);
  
  public function clear(ICacheCategory $category);
}