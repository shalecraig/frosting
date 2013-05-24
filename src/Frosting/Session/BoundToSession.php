<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Session;

use Frosting\DependencyInjection\Generator\IServiceContainerGeneratorAnnotation;
use Frosting\DependencyInjection\Generator\ContainerGenerator;

/**
 * Description of BoundToSession
 *
 * @Annotation
 */
class BoundToSession  implements IServiceContainerGeneratorAnnotation
{
  
  public function __construct()
  {
  }

  /**
   * @param mixed $object
   * @param string $methodName
   * @param Listen $annotation 
   */
  public function generateContainer(ContainerGenerator $generator, $serviceName, $attributeName)
  {
    $method = $generator->getServiceGetterMethod($serviceName);
    $currentCode = $method->getCode();
    $serviceBounderAssignation = '
    $sessionServiceBounder = $serviceContainer->getServiceByName("sessionServiceBounder");
';
    if(strpos($currentCode, $serviceBounderAssignation) === false) {
      $currentCode .= $serviceBounderAssignation;
    }
    $currentCode .= '
    $sessionServiceBounder->addBindingAttribute("' . $serviceName . '","' . $attributeName . '");
';
    $restoreFromSession = '
    $sessionServiceBounder->restoreFromSession($service,"' . $serviceName . '");
';
    $finalCode = str_replace($restoreFromSession, "", $currentCode);
    $finalCode .= $restoreFromSession;
    
    $method->setCode($finalCode);
  }
}