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
   * @var \Frosting\IService\View\IViewRendererService[] 
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
  
  public function canRender($file) 
  {
    foreach($this->renderers as $renderer) {
      if($renderer->canRender($file)) {
        return true;
      }
    }
    
    return false;
  }
  
  /**
   * @param \Frosting\IService\View\IViewRendererService[] $renderers
   * 
   * @Inject(renderers="@viewRenderer")
   */
  public function setRenderers($renderers)
  {
    $currentObject = $this;
    //We remove the current object since it is tag and we don't want
    //a infinite loop on the method that iterrate trough the renderers
    $this->renderers = array_values(
      array_filter(
        $renderers,
        function($renderer) use ($currentObject) {
          return $renderer != $currentObject;
        }
      )
    );
  }
  
  public function getExtensions() 
  {
    $extensions = array();
    foreach($this->renderers as $renderer) {
      $extensions = array_merge($extensions, $renderer->getExtensions());
    }
    
    return array_values(array_unique($extensions));
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
