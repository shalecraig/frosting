<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework;

use \Frosting\DependencyInjection\ServiceContainer;

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
    if(!array_key_exists('services', $this->configuration)) {
      $this->configuration["services"] = array();
    }
    $this->serviceContainer = $this->loadServiceContainer($this->configuration['services']);
  }
  
  public function loadServiceContainer($configuration)
  {
    return ServiceContainer::factory($configuration);
  }
  
  public function getServiceContainer()
  {
    return $this->serviceContainer;
  }
}
