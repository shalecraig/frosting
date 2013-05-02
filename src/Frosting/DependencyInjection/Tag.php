<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\DependencyInjection\Generator\IServiceContainerGeneratorAnnotation;
use Frosting\DependencyInjection\Generator\ContainerGenerator;

/**
 * @Annotation
 */
class Tag extends \Frosting\IService\DependencyInjection\Tag implements IServiceContainerGeneratorAnnotation
{
  public function generateContainer(ContainerGenerator $generator, $serviceName, $methodName)
  {
    $generator->tagService($serviceName, $this->getTagName());
  }
}
