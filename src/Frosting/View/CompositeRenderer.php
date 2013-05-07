<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Frosting\IService\View\IViewRendererService;
use Frosting\Framework\Frosting;

/**
 * Description of CompositeRenderer
 *
 * @author Martin
 */
class CompositeRenderer implements IViewRendererService
{
  /**
   * @var \Frosting\IService\View\IRenderer[] 
   */
  private $renderers = array();
  
  /**
   * @param string $file
   * @param array $parameters
   * 
   * @return string
   */
  public function render($file, array $parameters = array())
  {
    foreach($this->renderers as $renderer) {
      try {
        return $renderer->render($file, $parameters);
      } catch (\Exception $e) {
        
      }
    }
  }
  
  /**
   * @param type $renderers
   */
  public function setRenderers($renderers)
  {
    $this->renderers = array_diff($renderers,array($this));
  }
  
  /**
   * @param mixed $configuration
   * @return IViewRenderer
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,'routing');
  }
}
