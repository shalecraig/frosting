<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use \Frosting\IService\View\IViewRendererService;

/**
 * Description of PhpRenderer
 *
 * @author Martin
 */
class PhpRenderer implements IViewRendererService 
{
  public function render($file, array $parameters = array()) 
  {
    extract($parameters);

    ob_start();

    try {
      include $file;
    } catch (Exception $e) {
      ob_end_clean();
      throw $e;
    }
    return ob_get_clean();
  }
}
