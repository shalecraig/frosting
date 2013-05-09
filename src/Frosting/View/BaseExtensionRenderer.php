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
  
  protected function setExtensions($extensions)
  {
    $this->extensions = array_map('strtolower', $extensions);
  }
  
  public function canRender($file) 
  {
    $pathinfo = pathinfo($file, PATHINFO_EXTENSION);
    if(!isset($pathinfo['extension'])) {
      return false;
    }
    
    return in_array(strtolower($pathinfo['extensions']), $this->extensions);
  }
}
