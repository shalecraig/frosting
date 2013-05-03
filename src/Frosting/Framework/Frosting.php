<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework;

/**
 * Description of Frosting
 *
 * @author Martin
 */
class Frosting 
{
  private $configuration;
  
  private $serviceContainer;
  
  public function __construct($configurationFile) 
  {
    $fileLoader = new ConfigurationFileLoader();
    $this->configuration = $fileLoader->load($configurationFile);

    if(!isset($this->configuration['services']['serviceContainer'])) {
      $this->configuration = $fileLoader->load(
        array_merge(
          array('imports'=>array(__DIR__ . '/../DependencyInjection/frosting.json')),
          $this->configuration
        )
      );
    }

    $this->serviceContainer = $this->loadServiceContainer($this->configuration['services']);
  }
  
  /**
   * @param type $configuration
   * 
   * @return \Frosting\IService\DependencyInjection\IServiceContainer
   */
  protected function loadServiceContainer($configuration)
  {
    $class = $configuration['serviceContainer']['class'];
    return new $class($configuration);
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
