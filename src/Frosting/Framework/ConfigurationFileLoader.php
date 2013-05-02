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
    if(is_array($filename)) {
      return $filename;
    }
    
    if(!file_exists($filename)) {
      return null;
    }
    
    ob_start();
    include($filename);
    $content = ob_get_clean();
    
    $result = json_decode($content,true);
    
    if(array_key_exists('imports', $result)) {
      $result = array_deep_merge($this->imports($result['imports'],  dirname($filename)),$result);
      unset($result['imports']);
    }
    
    return $result;
  }
  
  private function imports($files,$basePath)
  {
    $result = array();
    foreach($files as $file) {
      switch(true) {
        case file_exists($basePath . DIRECTORY_SEPARATOR . $file):
          $result[] = $this->load($basePath . DIRECTORY_SEPARATOR . $file);
          break;
      }
    }
    
    return call_user_func_array('array_deep_merge', $result);
  }
}
