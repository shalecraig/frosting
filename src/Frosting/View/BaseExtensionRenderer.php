<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use \Frosting\IService\View\IViewRendererService;

/**
 * Description of BaseExtensionRenderer
 *
 * @author Martin
 */
abstract class BaseExtensionRenderer implements IViewRendererService
{
  private $extensions;
  
  /**
   * @var \Frosting\View\FileSystemLoader
   */
  private $fileLoader;
  
  /**
   * @param \Frosting\View\FileSystemLoader $loader
   * 
   * @Inject
   */
  public function setFileLoader(FileSystemLoader $viewFileLoader)
  {
    $this->fileLoader = $viewFileLoader;
  }
  
  /**
   * @return \Frosting\View\FileSystemLoader
   */
  public function getFileSystemLoader()
  {
    return $this->fileLoader;
  }
  
  protected function setExtensions($extensions)
  {
    $this->extensions = array_map('strtolower', $extensions);
  }
  
  public function getExtensions() 
  {
    return $this->extensions;
  }
  
  public function canRender($file) 
  {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    if(!$extension) {
      return false;
    }

    return in_array(strtolower($extension), $this->extensions) && $this->fileLoader->exists($file);
  }
}
