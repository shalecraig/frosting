<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\EventDispatcher;

use Frosting\DependencyInjection\IServiceContainerGeneratorAnnotation;
use Frosting\IService\EventDispatcher\IEventDispatcherService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Annotation\ParsingNode;
use Frosting\DependencyInjection\Definition;

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
  
  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName)
  {
    $generator->getDefinition(IEventDispatcherService::FROSTING_SERVICE_NAME)
      ->addCodeInitialization('
  $service->addListener(
    "' . $this->getEventName() . '",
    function(\Frosting\IService\EventDispatcher\IEvent $event) use ($serviceContainer) {
      $listener = array($serviceContainer->getServiceByName("' . $serviceName .'"),"' . $parsingNode->getContextName() . '");
      $serviceContainer->getServiceByName(\Frosting\IService\Invoker\IInvokerService::FROSTING_SERVICE_NAME)
        ->invoke($listener,$event->getParameters(),array($event, $event->getSubject()));
    },
    "' . $this->getPriority() . '"
  );
');
  }
}