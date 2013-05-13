<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\AssetManager;

use Frosting\IService\FileSystem\IFileSystemService;

/**
 * Description of FilePersister
 *
 * @author Martin
 */
class FilePersister 
{
  private $rootDirectory;
  
  /**
   * @var \Frosting\IService\FileSystem\IFileSystemService
   */
  private $fileSystem;
  
  /**
   * @param \Frosting\IService\FileSystem\IFileSystemService $fileSystem
   * 
   * @Inject
   */
  public function initialize(IFileSystemService $fileSystem)
  {
    $this->fileSystem = $fileSystem;
  }
  
  /**
   * @param strin $directory
   * 
   * @Inject(directory="$[assetManager][rootDirectory]")
   */
  public function setRootDirectory($directory)
  {
    $this->rootDirectory = $directory;
  }
  
  public function persist($filePath, $content)
  {
    $this->fileSystem->dumpFile($this->rootDirectory . '/' . $filePath, $content);
    return true;
  }
  
  public function recover($path)
  {
    return file_get_contents($this->rootDirectory . $path);
  }
  
  public function exists($path)
  {
    return $this->fileSystem->exists($this->rootDirectory . $path);
  }
}
