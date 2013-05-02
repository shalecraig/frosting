<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework\EventDispatcher;

use Frosting\DependencyInjection\Generator\IServiceContainerGeneratorAnnotation;
use Frosting\DependencyInjection\Generator\ContainerGenerator;


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
    $this->eventName = !is_array($values) ? $values : $values['eventName'];
    $this->priority = is_array($values) && isset($values['priority']) ? $values['priority'] : 0;
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
    $method = $generator->getServiceGetterMethod("event_dispatcher");
    $code = '
    $service->connect("' . $eventName . '",function(\core\event\IEvent $event) use ($serviceContainer) {
      $listener = array($serviceContainer->getService("' . $serviceName .'"),"' . $methodName . '");
      $arguments = \Woozworld\Application\MethodParametersMapper::getArguments($event->getParameters(), $listener, array($event, $event->getSubject()));
      call_user_func_array($listener, $arguments);
    });
';
    $method->setCode($method->getCode() . "\n$code");
  }
}