<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Session;

use Frosting\DependencyInjection\IServiceContainerGeneratorAnnotation;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Annotation\ParsingNode;
use Frosting\DependencyInjection\Definition;

/**
 * Description of BoundToSession
 *
 * @Annotation
 */
class BoundToSession implements IServiceContainerGeneratorAnnotation
{
  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName)
  {
    $currentCode = $definition->getCodeInitalization();
    $serviceBinderAssignation = '
    $sessionServiceBinder = $serviceContainer->getServiceByName("sessionServiceBinder");
';
    if(strpos($currentCode, $serviceBinderAssignation) === false) {
      $currentCode .= $serviceBinderAssignation;
    }
    $currentCode .= '
    $sessionServiceBinder->addBindingAttribute("' . $serviceName . '","' . $parsingNode->getContextName() . '");
';
    $restoreFromSession = '
    $sessionServiceBinder->restoreFromSession($service,"' . $serviceName . '");
';
    $finalCode = str_replace($restoreFromSession, "", $currentCode);
    $finalCode .= $restoreFromSession;
    
    $definition->setCodeInitialization($finalCode);
  }
}