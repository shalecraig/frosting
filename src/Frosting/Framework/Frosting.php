<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\DependencyInjection\FrostingCompilerPass;
use Frosting\DependencyInjection\PhpDumper;
use Symfony\Component\Config\ConfigCache;

/**
 * Description of Frosting
 *
 * @author Martin
 */
class Frosting 
{
  private $serviceContainer;
  
  public function __construct($configurationFile) 
  {
    $this->serviceContainer = $this->loadServiceContainer($configurationFile);
  }
  
  /**
   * @param type $configuration
   * 
   * @return \Frosting\IService\DependencyInjection\IServiceContainer
   */
  protected function loadServiceContainer($configurationFile)
  {
    $escaping = md5(serialize($configurationFile));
    $class = 'ServiceContainer' . $escaping;
    $file = __DIR__ . '/../../../cache/' . $escaping . '/' . $class . '.php';
    $containerConfigCache = new ConfigCache($file, true);
    if (!class_exists($class)) {
      if (!$containerConfigCache->isFresh()) {
        $container = new ContainerBuilder();
        $frostingCompilerPass = new FrostingCompilerPass($configurationFile);
        $container->addCompilerPass($frostingCompilerPass);
        $container->compile();
        $dumper = new PhpDumper($container);
        $containerConfigCache->write(
          $dumper->dump(array('class' => $class, 'frosting' => $frostingCompilerPass->getConfiguration())),
          $container->getResources()
        );
        
      }
      require($file);
    }
    $serviceContainer = new $class();
    $serviceContainer->initialize();
    return $serviceContainer;
  }
  
  /**
   * 
   * @return \Frosting\IService\DependencyInjection\IServiceContainer
   */
  public function getServiceContainer()
  {
    return $this->serviceContainer;
  }
  
  /**
   * 
   * @param type $configurationFile
   * @return Frosting
   */
  public static function factory($configurationFile) 
  {
    return new static($configurationFile);
  }
  
  /**
   * This is a method to use to initialize a stand alone service. You should
   * use a new Frosting application if you want to access the service container
   * that have been genenerated since the reference will be "lost" with
   * this method.
   * 
   * @param mixed $configuration
   * @param string $serviceName
   * @return mixed
   */
  public static function serviceFactory($configuration, $serviceName)
  {
    $frosting = new static($configuration);
    return $frosting->getServiceContainer()->getServiceByName($serviceName);
  }
}
