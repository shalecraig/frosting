<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;
use \Frosting\DependencyInjection\Generator\IServiceContainerGeneratorAnnotation;
use \Frosting\DependencyInjection\Generator\ContainerGenerator;

/**
 * Description of Route
 * 
 * @author Martin
 * 
 * @Annotation
 */
class Route extends BaseRoute implements IServiceContainerGeneratorAnnotation
{
  public function generateContainer(ContainerGenerator $generator, $serviceName, $methodName) 
  {    
    $defaults = $this->getDefaults();
    $defaults['_service'] = array('name'=>$serviceName,'method'=>$methodName);
    $method = $generator->getServiceGetterMethod('routing');
    $code = $method->getCode();
    $code .= '
    $service->addRoute(
      ' . var_export($this->getName(),true) . ',
      ' . var_export($this->getPath(),true) . ',
      ' . var_export($defaults,true) . ',
      ' . var_export($this->getRequirements(),true) . ',
      ' . var_export($this->getOptions(),true) . ',
      ' . var_export($this->getHost(),true) . ',
      ' . var_export($this->getSchemes(),true) . ',
      ' . var_export($this->getMethods(),true) . '
    );
';
    $method->setCode($code);
  }
}
