<?php

namespace Frosting\EventDispatcher;

use \Frosting\IService\EventDispatcher\IEventDispatcherService;
use \Symfony\Component\EventDispatcher\EventDispatcher as ProxiedEventDispatcher;
use \Frosting\IService\EventDispatcher\IEvent;

class EventDispatcher implements IEventDispatcherService
{
  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher 
   */
  private $eventDispatcher = null;
  
  public function __construct()
  {
    $this->eventDispatcher = new ProxiedEventDispatcher();
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
      call_user_func($listener, $event);
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
}