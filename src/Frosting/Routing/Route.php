<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Routing;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;
use Frosting\DependencyInjection\IServiceContainerGeneratorAnnotation;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Annotation\ParsingNode;
use Frosting\DependencyInjection\Definition;

/**
 * Description of Route
 * 
 * @author Martin
 * 
 * @Annotation
 */
class Route extends BaseRoute implements IServiceContainerGeneratorAnnotation
{
  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName)
  {
    $defaults = $this->getDefaults();
    $defaults['_service'] = array('name'=>$serviceName,'method'=>$parsingNode->getContextName());
    $arguments = array(
      $this->getName(),
      $this->getPath(),
      $defaults,
      $this->getRequirements(),
      $this->getOptions(),
      $this->getHost(),
      $this->getSchemes(),
      $this->getMethods()
    );
    $generator->getDefinition("routing")->addMethodCall('addRoute', $arguments);
  }
}
