<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\View;

use Twig_Loader_Array;;
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
      $this->twig = new Twig_Environment(new Twig_Loader_Array(array()), array(
          'cache' => $this->cacheDirectory,
      ));
    }

    return $this->twig;
  }
  
  public function render($file, array $parameters = array()) 
  {
    $twig = $this->getTwig();
    $twig->getLoader()->setTemplate(
      $file, 
      file_get_contents($this->getFileSystemLoader()->getFullPath($file))
    );
    return $twig->render($file, $parameters);
  }
}
