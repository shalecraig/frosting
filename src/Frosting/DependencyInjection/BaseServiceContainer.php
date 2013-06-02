<?php

namespace Frosting\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;
use Frosting\Framework\Frosting;
use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\IService\DependencyInjection\ILifeCycleAware;

abstract class BaseServiceContainer extends Container implements IServiceContainer
{
  protected $tags = array();
  protected $disabled = array();
  protected $serviceConfigurations = array();
  
  /**
   *
   * @var \Frosting\IService\DependencyInjection\ILifeCycleAware[]
   */
  private $startedServices = array();
  
  public function initialize()
  {
    $this->getServicesByTag("autoStart");
    register_shutdown_function(array($this,'shutdown'));
  }
  
  public function shutdown()
  {
    foreach($this->startedServices as $service) {
      $service->serviceShutdown();
    }
  }
  
  protected function getThis()
  {
    return $this;
  }
  
  public function getServicesByTag($tag) {
     if(!isset($this->tags[$tag])) {
       return array();
     }

     $services = array();
     foreach($this->tags[$tag] as $serviceName) {
       $services[] = $this->getServiceByName($serviceName);
     }

     return $services;
   }

   public function get($name, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) 
   {
     if(in_array($name,$this->disabled)) {
       throw new \Frosting\IService\DependencyInjection\ServiceDisabledException("The service named [$name] is disabled.");
     }
     if(!$this->has($name)) {
       throw new \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException("The service named [$name] does not exists.");
     }

     $service = parent::get($name);

     if($service instanceof ILifeCycleAware && !in_array($service,$this->startedServices)) {
       $this->startedServices[spl_object_hash($service)] = $service;
       $service->serviceStart();
     }
     
     return $service;
   }
   
   public function getServiceByName($name) 
   {
     return $this->get($name);
   }

   public function getServiceNames()
   {
     return $this->getServiceIds();
   }

   public function getServiceConfiguration($name)
   {
     if(!$this->has($name)) {
       throw new \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException("The service named [$name] does not exists.");
     }

     return isset($this->serviceConfigurations[$name]) ? $this->serviceConfigurations[$name] : null;
   }
   
  /**
   * @param mixed $configuration
   * @return IServiceContainer
   */
  public static function factory(array $configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }

    return Frosting::serviceFactory($configuration,"serviceContainer");
  }
}
