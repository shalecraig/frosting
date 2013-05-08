<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Frosting\IService\View\IViewRendererService;
use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Description of TwigRenderer
 *
 * @author Martin
 */
class TwigRenderer implements IViewRendererService
{
  /**
   * @var Twig_Environment 
   */
  private $twig = null;
  
  /**
   * @var string 
   */
  private $viewDirectory = null;
  
  /**
   * @var string 
   */
  private $cacheDirectory = null;
  
  /**
   * 
   * @param string $cacheDirectory
   * @param string $viewDirectory
   * 
   * @Inject(cacheDirectory="$[configuration][generatedDirectory]",viewDirectory="$[viewRenderer][viewDirectory]")
   */
  public function initialize($cacheDirectory,$viewDirectory)
  {
    $this->viewDirectory = $viewDirectory;
    $this->cacheDirectory = $cacheDirectory;
  }
  
  /**
   * @return Twig_Environment
   */
  private function getTwig() {
    if (is_null($this->twig)) {
      $loader = new Twig_Loader_Filesystem($this->viewDirectory);
      $this->twig = new Twig_Environment($loader, array(
          'cache' => $this->cacheDirectory,
      ));
    }

    return $this->twig;
  }
  
  public function render($file, array $parameters = array()) 
  {
    return $this->getTwig()->render($file . '.twig', $parameters);
  }
}
