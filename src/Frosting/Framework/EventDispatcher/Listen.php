<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework\EventDispatcher;

use Frosting\DependencyInjection\Generator\IServiceContainerGeneratorAnnotation;
use Frosting\DependencyInjection\Generator\ContainerGenerator;
use Frosting\IService\EventDispatcher\IEventDispatcherService;


/**
 * Description of Inject
 *
 * @Annotation
 */
class Listen implements IServiceContainerGeneratorAnnotation
{
  /**
   * @var string
   */
  private $eventName;
  
  /**
   * @var int
   */
  private $priority;
  
  public function __construct($values)
  {
    $this->eventName = isset($values['value']) ? $values['value'] : $values['eventName'];
    $this->priority = isset($values['priority']) ? $values['priority'] : 0;
  }
  
  public function getEventName()
  {
    return $this->eventName;
  }
  
  public function getPriority()
  {
    return $this->priority;
  }
  
  /**
   * @param mixed $object
   * @param string $methodName
   * @param Listen $annotation 
   */
  public function generateContainer(ContainerGenerator $generator, $serviceName, $methodName)
  {
    self::connect($generator, $this->getEventName(), $serviceName, $methodName);
  }
  
  public static function connect(ContainerGenerator $generator, $eventName, $serviceName, $methodName)
  {
    $method = $generator->getServiceGetterMethod(IEventDispatcherService::FROSTING_SERVICE_NAME);
    $code = '
    $service->addListener("' . $eventName . '",function(\Frosting\IService\EventDispatcher\IEvent $event) use ($serviceContainer) {
      $listener = array($serviceContainer->getServiceByName("' . $serviceName .'"),"' . $methodName . '");
      $serviceContainer->getServiceByName(\Frosting\IService\Invoker\IInvokerService::FROSTING_SERVICE_NAME)
        ->invoke($listener,$event->getParameters(),array($event, $event->getSubject()));
    });
';
    $method->setCode($method->getCode() . "\n$code");
  }
}