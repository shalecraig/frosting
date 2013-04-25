<?php

namespace Frosting\Cache\File;

use Frosting\Cache\ICacheStorage;
use Frosting\IService\Cache\ValueNotFoundException;
use Frosting\Cache\ICacheCategory;
use Frosting\Cache\EntryContent;

use \Woozworld\Cache\StorageConfigurationException;

/**
 * File storage class
 */
class FileStorage implements ICacheStorage
{
  /**
   * @var string
   */
  private $baseDir;
  
  
  /**
   * @param array $config
   * 
   * @Frosting\IService\DependencyInjection\Inject(configuration="$")
   */
  public function initialize($configuration = array())
  {
    if(is_null($configuration)) {
      $configuration = array();
    }
    if (!isset($configuration['baseDir'])) {
      $configuration['baseDir'] = sys_get_temp_dir();
    }
    $this->baseDir = $configuration['baseDir'];
  }
  
  /**
   * Stores an entry content
   * @param string $entryName
   * @param EntryContent $content
   * @param ICategory $category
   * @return ICacheStorage $this
   */
  public function store($entryName, EntryContent $content, ICacheCategory $category)
  {
    $file = $this->prepareBaseDir()->getKey($entryName, $category);
    
    if (!@file_put_contents($file, serialize($content))) {
      throw new IOException(sprintf('Error writing file %s', $file));
    }
    chmod($file, 0777);
        
    return $this;
  }
  
  /**
   * Throws a ValueNotFoundException if, well, it's not in the storage
   * @param string $entryName
   * @param ICategory $category
   * @return EntryContent
   */
  public function retrieve($entryName, ICacheCategory $category)
  {
    $file = $this->getKey($entryName, $category);
    
    if (!file_exists($file))    {
      throw new ValueNotFoundException($file);
    }
    $content = @file_get_contents($file);
    
    if (!$content || !($content = @unserialize($content))) {
      throw new ValueNotFoundException();
    }
    
    return $content;
  }
    
  /**
   * Remove an entry
   * @param string $entryName
   * @param ICategory $category
   * @return ICacheStorage $this
   */
  public function remove($entryName, ICacheCategory $category)
  {
    $file = $this->prepareBaseDir()->getKey($entryName, $category);
    
    if (file_exists($file) && !@unlink($file)) {
      throw new IOException(sprintf('Error removing file %s', $file));
    }
        
    return $this;
  }
  
  /**
   * Get the memcache key for a given category Id
   * @param string $entryName
   * @param integer $categoryId
   * @param string $categoryName
   * @return string
   */
  private function getKey($entryName, ICacheCategory $category)
  {
    $categoryName = $category->getSegregationPrefix() . '_' . $category->getName();
    $categoryId = $category->getVersion();
    $file = preg_replace('/[^0-9a-zA-Z_\.-]/', '_', $entryName);
    $path = $this->baseDir . DIRECTORY_SEPARATOR . $categoryName;
    if (!is_dir($path)) {
      mkdir($path, 02777, true);
      chmod($path, 02777);
      
      //Also chmod parent directories if possible
      @chmod($this->baseDir, 02777);
      @chmod(dirname($this->baseDir), 02777);
      @chmod(dirname(dirname($this->baseDir)), 02777);
    }
    $path .= DIRECTORY_SEPARATOR . $categoryId;
    if (!is_dir($path)) {
      mkdir($path, 02777, true);
      chmod($path, 02777);
    }
    
    return $path . DIRECTORY_SEPARATOR .  $file;
  }
  
  public function clear(ICacheCategory $category) 
  {
    
  }
  
  /**
   * In case of a write opeartion, prepares the base dir. Not done in the 
   * constructor to avoid disk access for ALL calls
   * @return $this
   */
  protected function prepareBaseDir()
  {
    if (!is_dir($this->baseDir)) {
      if (!@mkdir($this->baseDir, 02777, true)) {
        throw new StorageConfigurationException(sprintf('Cannot create base dir %s', $this->baseDir));
      }
    } else if (!is_writable($this->baseDir)) {
      throw new StorageConfigurationException(sprintf('Base dir %s not writable', $this->baseDir));
    }
    
    return $this;
  }
}