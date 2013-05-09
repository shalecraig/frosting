<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Description of TwigRenderer
 *
 * @author Martin
 */
class TwigRenderer extends BaseExtensionRenderer
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
  
  public function __construct() 
  {
    $this->setExtensions(array('twig'));
  }
  
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
    return $this->getTwig()->render($file, $parameters);
  }
}
