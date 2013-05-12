<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\DependencyInjection\IServiceContainer;
use Frosting\Framework\Frosting;
use Frosting\DependencyInjection\Generator\ContainerGenerator;

/**
 * Description of ServiceContainer
 *
 * @author Martin
 */
class ServiceContainer implements IServiceContainer
{
  /**
   * @var \Frosting\IService\DependencyInjection\IServiceContainer 
   */
  public $generatedContainer = null;
  
  public function __construct(array $configuration = array())
  {
    $neededServices = array(
      'annotationParser',
      'objectFactory',
      'configuration',
      'fileSystem',
    );
    
    $services = array('serviceContainer'=>$this);
    
    foreach($neededServices as $serviceName) {
      if(!isset($configuration[$serviceName]['class'])) {
        throw new \RuntimeException('The service named [' . $serviceName . '] is required for the service container');
      }
      $class = $configuration[$serviceName]['class'];
      
      if($serviceName !== constant($class . '::FROSTING_SERVICE_NAME')) {
        throw new \RuntimeException('The class [' . $class . '] for service named [' . $serviceName . '] is not valid');
      }

      $services[$serviceName] = new $class();
    }
    
    $services['annotationParser']->addNamespace("Frosting\DependencyInjection");
    $services['annotationParser']->addNamespace("Frosting\Framework\EventDispatcher");
    $services['annotationParser']->addNamespace("Frosting\ObjectFactory");

    foreach($configuration as $serviceName => $serviceParameter) {
      $serviceConfiguraton = isset($serviceParameter['configuration']) ? $serviceParameter['configuration'] : null;
      $services['configuration']->merge(array($serviceName=>$serviceConfiguraton));
    }

    $generator = new ContainerGenerator($services['annotationParser'],$services['fileSystem'],$configuration);
    $path = $services['configuration']->get("[configuration][generatedDirectory]");
    $className = $generator->generate(
      $path,
      $services['configuration']->get("[configuration][debug]")
    );
    
    require_once($path . '/' . $className . '.php');
 
    $container = $services['serviceContainer'];
    
    $container->generatedContainer = new $className($services);
    $objectFactory = $container->generatedContainer->getServiceByName("objectFactory");
    $objectFactory->registerClassCreator(
      new \Frosting\ObjectFactory\AnnotationClassCreator($services['annotationParser'])
    );
    
    $objectFactory->registerObjectBuilder(
      new ServiceContainerReferenceObjectBuilder($this->generatedContainer)
    );
    
    $services['objectFactory']->setServiceContainer($container);
    
    $container->initialize();
    return $container;
  }
  
  private function initialize() 
  {
    $objectFactory = $this->generatedContainer->getServiceByName("objectFactory");
    foreach($this->getServicesByTag("objectFactory.builder") as $service) {
      $objectFactory->registerObjectBuilder($service);
    }
  }
  
  public function shutdown() 
  {
    $this->generatedContainer->shutdown();  
  }
 
  public function getServiceByName($name)
  {
    return $this->generatedContainer->getServiceByName($name);
  }
  
  /**
   * Get all services that are tag
   * 
   * @param string $tag The tag the service must be done with
   * 
   * @return array of service objects 
   */
  public function getServicesByTag($tag) 
  {
    return $this->generatedContainer->getServicesByTag($tag);
  }

  public function getServiceNames() 
  {
    return $this->generatedContainer->getServiceNames();
  }
  
  public function getServiceConfiguration($name) 
  {
    return $this->generatedContainer->getServiceConfiguration($name);
  }
  
  /**
   * @param mixed $configuration
   * @return IObjectFactoryService
   */
  public static function factory(array $configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }

    return Frosting::serviceFactory($configuration,"serviceContainer");
  }
}
