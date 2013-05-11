<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\AssetManager\Twig;

use Twig_Extension;
use Frosting\AssetManager\Manager;

/**
 * Description of TwigExtension
 *
 * @author Martin
 * 
 * @Tag("twigRenderer.twigExtension")
 */
class AssetManagerExtension extends Twig_Extension
{
  /**
   * @var \Frosting\AssetManager\Manager
   */
  private $assetManager;
  
  /**
   * @param \Frosting\AssetManager $assetManager
   * 
   * @Inject
   */
  public function setAssetManager(Manager $assetManager)
  {
    $this->assetManager = $assetManager;
  }
  
  public function getFunctions() {
    return array(
        new \Twig_SimpleFunction('frosting_asset',array($this,'getHtmlTags'))
    );
  }
  
  public function getHtmlTags()
  {
    $files = func_get_args();
    return implode("\n",$this->assetManager->getHtmTags($files));
  }
  
  public function getName()
  {
    return 'frostingAssetManager';
  }
}
