<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework\EventDispatcher;

use Frosting\DependencyInjection\ServiceContainer;
use Frosting\IService\EventDispatcher\IEvent;

/**
 * Description of ListenAnnotationConnector
 *
 * @Tag("AnnotationConnector.\Framework\EventDispatcher\Listen")
 * 
 * @author Martin
 */
class ListenAnnotationConnector 
{
  /**
   * @param \Frosting\Framework\EventDispatcher\Listen $annotation
   * @param \Frosting\DependencyInjection\ServiceContainer $serviceContainer
   * @param string $serviceName
   * @param string $methodName
   */
  public function connectAnnotation($annotation, ServiceContainer $serviceContainer, $serviceName, $methodName = null)
  {
    $eventDispatcher = $serviceContainer->getServiceByName("evenDispatcher");
    /* @var $eventDispatcher \Frosting\IService\EventDispatcher\IEventDispatcherService */
    $eventDispatcher->addListener(
      $annotation->getEventName(), 
      function(IEvent $event) use ($serviceContainer,$serviceName,$methodName) {
        $service = $serviceContainer->getServiceByName($serviceName);
        call_user_func(array($service,$methodName),$event);
      },
      $annotation->getPriority()
    );
  }
}
