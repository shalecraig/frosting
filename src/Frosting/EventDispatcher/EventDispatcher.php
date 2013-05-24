<?php

namespace Frosting\EventDispatcher;

use Frosting\IService\EventDispatcher\IEventDispatcherService;
use Symfony\Component\EventDispatcher\EventDispatcher as ProxiedEventDispatcher;
use Frosting\IService\EventDispatcher\IEvent;
use Frosting\Framework\Frosting;
use Frosting\IService\Invoker\IInvokerService;

/**
 * @Tag("autoStart")
 */
class EventDispatcher implements IEventDispatcherService
{
  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher 
   */
  private $eventDispatcher = null;
  
  /**
   * @var \Frosting\IService\Invoker\IInvokerService
   */
  private $invoker = null;
  
  public function __construct()
  {
    $this->eventDispatcher = new ProxiedEventDispatcher();
  }
  
  /**
   * @param Frosting\IService\Invoker\IInvokerService
   * 
   * @Inject
   */
  public function setInvoker(IInvokerService $invoker) 
  {
    $this->invoker = $invoker;
  }
  
  public function addListener($eventName, $listener, $priority = 0) 
  {
    $this->eventDispatcher->addListener($eventName, $listener, $priority);
  }

  public function dispatch($eventName, $subject = null, array $parameters = array()) 
  {
    $event = new Event($eventName, $this, $subject, $parameters);

    $this->doDispatch($this->getListeners($eventName), $event);

    return $event;
  }

  private function doDispatch($listeners, IEvent $event) 
  {
    foreach ($listeners as $listener) {
      $this->invoker->invoke($listener,$event->getParameters(),array($event, $event->getSubject()));
      if ($event->isPropagationStopped()) {
        break;
      }
    }
  }

  public function getListeners($eventName = null) 
  {
    return $this->eventDispatcher->getListeners($eventName);
  }

  public function hasListeners($eventName = null) 
  {
    return $this->eventDispatcher->hasListeners($eventName);  
  }

  public function removeListener($eventName, $listener) 
  {
    return $this->eventDispatcher->removeListener($eventName, $listener) ;  
  }
  
  /**
   * @param mixed $configuration
   * @return IEventDispatcherService
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,self::FROSTING_SERVICE_NAME);
  }
}