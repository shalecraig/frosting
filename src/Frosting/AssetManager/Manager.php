<?php

namespace Frosting\AssetManager;

use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\HttpAsset;
use Assetic\Asset\FileAsset;
use Assetic\Filter\ScssphpFilter;
use Frosting\Framework\Frosting;
use Assetic\Asset\AssetCollection;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\LessphpFilter;
use Frosting\IService\AssetManager\IUrlBuilder;


/**
 * Description of Manager
 *
 * @author 
 */
class Manager 
{
  private $configuration;
  
  /**
   * @var FilePersister
   */
  private $filePersister;
  
  /**
   * @var \Frosting\IService\AssetManager\IUrlBuilder 
   */
  private $urlBuilder;
  
  const WATCH_DIRECTORY_DELAY = 5;
  
  /**
   * @param array $configuration
   * 
   * @Inject(configuration="$")
   */
  public function initialize(array $configuration, FilePersister $assetManagerFilePersister, IUrlBuilder $urlBuilder)
  {
    $this->configuration = $configuration;
    $this->filePersister = $assetManagerFilePersister;
    $this->urlBuilder = $urlBuilder;
  }
  
  /**
   * @Route(path="/frosting/asset/*")
   * @param type $path
   */
  public function getContent($path)
  {
    return $this->filePersister->recover($path);
  }
  
  /**
   * Gets the absolute URL to the target asset
   * 
   * @param AssetInterface $asset
   * @param string $fileType
   * @return string Absolute URI 
   */
  public function getAssetUrl(AssetInterface $asset, $fileType = 'css')
  {
    $key = $this->getCacheKey($asset);
    
    $filePath = $asset->getSourcePath();
    if(!$filePath) {
      $filePath = '/aggregation.';
    }
    
    $targetPath = 'frosting/asset'.$filePath. '.' . $key . '.' . $fileType;
    
    if(!$this->filePersister->exists($targetPath)) {
      $asset->setTargetPath($targetPath);
      $this->applyFilters($asset, $fileType);
      $asset->load();    
      $this->filePersister->persist($targetPath,$asset->dump());
    }
    
    return $this->getUrl('/' . $targetPath);
  }
  
  public function getUrl($relativePath)
  {
    return $this->urlBuilder->getUrl($relativePath);
  }
  
  private function applyFilters(AssetInterface $asset, $fileType)
  {
    if($fileType == 'css') {
      $asset->ensureFilter(new CssRewriteFilter()); //NECESSARY FOR IMAGE PATHS
    }
  }
  
  /**
   * @param AssetInterface $asset
   * @return type 
   */
  protected function getCacheKey(AssetInterface $asset) {
    $cacheKey = '';

    if ($asset instanceof AssetCollectionInterface) {
      foreach ($asset->all() as $childAsset) {
        $cacheKey .= $childAsset->getSourcePath();
        $cacheKey .= $childAsset->getLastModified();
      }
    } else {
      $cacheKey .= $asset->getSourcePath();
      $cacheKey .= $asset->getLastModified();
    }

    foreach ($asset->getFilters() as $filter) {
      if ($filter instanceof HashableInterface) {
        $cacheKey .= $filter->hash();
      } else {
        $cacheKey .= serialize($filter);
      }
    }

    return md5($cacheKey);
  }
  
  /**
   * Returns a FileAsset object from the filepath and request
   * 
   * @param string $filepath
   * @param \sfRequest $request
   * @return \Assetic\Asset\FileAsset 
   */
  public function getFileAsset($filepath, $path = null)
  {
    $rootDirectory = $this->getRootDirectory();
    if(is_null($path)) {
      $path = $this->getPath($filepath);
    }
    
    return new FileAsset($rootDirectory . $path, array(),$rootDirectory,$path);
  }
  
  
  public function getHtmTags($files)
  {
    $cssFiles = array();
    $jsFiles = array();

    foreach ($files as $file) {
      $path = $this->getPath($file);
      switch (true) {
        case strpos($file, '://') !== false:
          $asset = new HttpAsset($path);
          break;
        default:
          $asset = $this->getFileAsset($file, $path);
          break;
      }
      
      switch(pathinfo($path, PATHINFO_EXTENSION)) {
        case 'less':
          $asset->ensureFilter(new LessphpFilter());
          $cssFiles[] = $asset;
          break;
        case 'scss':
          $asset->ensureFilter(new ScssphpFilter());
          $cssFiles[] = $asset;
          break;
        case 'css':
          $cssFiles[] = $asset;
          break;
        case 'js':
          $jsFiles[] = $asset;
          break;
        default:
          throw new \Exception('Not supported file [' . $file .']');
      }
    }
    
    if($this->configuration['aggregation'] === true) {
      $cssFiles = !empty($cssFiles)?array(new AssetCollection($cssFiles)):array();
      $jsFiles = !empty($jsFiles)?array(new AssetCollection($jsFiles)):array();
    }
    
    
    $tags = array();
   
    foreach($cssFiles as $file) {
      $tags[] = '<link rel="stylesheet" href="' . $this->getAssetUrl($file,'css') . '" type="text/css" />';
    }
    
    foreach($jsFiles as $file) {
      $tags[] = "<script src='" . $this->getAssetUrl($file,'js') . "'></script>";
    }
    
    return $tags;
  }

  private function getPath($source)
  {
    if (strpos($source, '://')) {
      return $source;
    }

    if (0 !== strpos($source, '/')) {
      throw new \Exception('must be from root');
    }

    $query_string = '';
    if (false !== $pos = strpos($source, '?')) {
      $query_string = substr($source, $pos);
      $source = substr($source, 0, $pos);
    }
    
    return $source;
  }
  
  private function getRootDirectory()
  {
    return $this->configuration['rootDirectory'];
  }
  
  /**
   * @param mixed $configuration
   * @return Manager
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,'assetManager');
  }
}
