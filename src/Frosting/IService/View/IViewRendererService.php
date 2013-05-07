<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\View;

/**
 * Description of IRenderer
 *
 * @author Martin
 * 
 * @Tag("viewRenderer")
 */
interface IViewRendererService 
{
  /**
   * @param type $file
   * @param array $parameters
   * 
   * @return string
   */
  public function render($file,array $parameters = array());
}
