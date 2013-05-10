<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Twig_Loader_Array;
use Twig_Loader_Chain;
use Twig_Environment;
use Twig_Loader_Filesystem;

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
  private $cacheDirectory = null;
  
  /**
   *
   * @var Twig_Loader_Array
   */
  private $arrayLoader = null;
  
  public function __construct() 
  {
    $this->setExtensions(array('twig'));
  }
  
  /**
   * 
   * @param string $cacheDirectory
   * @param string $viewDirectory
   * 
   * @Inject(cacheDirectory="$[configuration][generatedDirectory]")
   */
  public function initialize($cacheDirectory)
  {
    $this->cacheDirectory = $cacheDirectory;
  }
  
  /**
   * @return Twig_Environment
   */
  private function getTwig() {
    if (is_null($this->twig)) {
      $loader = new Twig_Loader_Chain();
      $this->arrayLoader = new Twig_Loader_Array(array());
      $loader->addLoader($this->arrayLoader);
      $loader->addLoader(
        new Twig_Loader_Filesystem($this->getFileSystemLoader()->getPaths())
      );
      $this->twig = new Twig_Environment($loader , array(
        'cache' => $this->cacheDirectory,
      ));
    }

    return $this->twig;
  }
  
  public function render($file, array $parameters = array()) 
  {
    $twig = $this->getTwig();
    if(file_exists($file)) {
      $this->arrayLoader->setTemplate($file, file_get_contents($file));
    }
    return $twig->render($file, $parameters);
  }
}
