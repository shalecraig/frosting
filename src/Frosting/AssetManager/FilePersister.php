<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\AssetManager;

/**
 * Description of FilePersister
 *
 * @author Martin
 */
class FilePersister 
{
  private $rootDirectory;
  
  /**
   * @param strin $directory
   * 
   * @Inject(directory="$[assetManager][rootDirectory]")
   */
  public function setRootDirectory($directory)
  {
    $this->rootDirectory = $directory;
  }
  
  public function persist($path, $content)
  {
    $dir = dirname($path);
    mkdir($this->rootDirectory . $dir, 0777, true);
    file_put_contents($this->rootDirectory . $path, $content);
    return true;
  }
  
  public function recover($path)
  {
    return file_get_contents($this->rootDirectory . $path);
  }
  
  public function exists($path)
  {
    return file_exists($this->rootDirectory . $path);
  }
}
