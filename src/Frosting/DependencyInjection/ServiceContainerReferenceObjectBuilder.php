<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\ObjectFactory\IObjectBuilder;
use Frosting\IService\DependencyInjection\IServiceContainer;
use ReflectionObject;

/**
 * Description of InjecterObjectBuilder
 *
 * @author Martin
 */
class ServiceContainerReferenceObjectBuilder implements IObjectBuilder
{
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private $serviceContainer;
  
  /**
   * @var ReflectionObject
   */
  private $reflectionObject;
  
  public function __construct(IServiceContainer $serviceContainer) 
  {
    $this->serviceContainer = $serviceContainer;
    $this->reflectionObject = new ReflectionObject($serviceContainer);
  }
  
  public function initializeObject($mixed, array $contextParameters = array())
  {
    if(!isset($contextParameters['serviceName'])) {
      return;  
    }
    
    $this->addServiceReference($contextParameters['serviceName'], $mixed);
  }
  
  private function addServiceReference($serviceName, $mixed)
  {
    $servicesProperty = $this->reflectionObject->getProperty('services');
    $servicesProperty->setAccessible(true);
    $services = $servicesProperty->getValue($this->serviceContainer);
    $services[$serviceName] = $mixed;
    $servicesProperty->setValue($this->serviceContainer, $services);
  }
}
