<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework;

/**
 * Description of Frosting
 *
 * @author Martin
 */
class ConfigurationFileLoader 
{
  public function load($filename)
  {
    if(!is_array($filename)) {
      if(!file_exists($filename)) {
        return null;
      }
      ob_start();
      include($filename);
      $content = ob_get_clean();
      $result = json_decode($content,true);
      $basePath = dirname($filename);
    } else {
      $basePath = null;
      $result = $filename;
    }
    
    if(array_key_exists('imports', $result)) {
      $result = array_deep_merge($this->imports($result['imports'],  $basePath),$result);
      unset($result['imports']);
    }
    
    return $result;
  }
  
  private function imports($files,$basePath)
  {
    $result = array();
    foreach($files as $file) {
      switch(true) {
        case file_exists($file):
          $result[] = $this->load($file);
          break;
        case !is_null($basePath) && file_exists($basePath . DIRECTORY_SEPARATOR . $file):
          $result[] = $this->load($basePath . DIRECTORY_SEPARATOR . $file);
          break;
      }
    }
    
    return call_user_func_array('array_deep_merge', $result);
  }
}
