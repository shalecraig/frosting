<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Annotation\ParsingNode;

/**
 * @Annotation
 */
class Tag extends \Frosting\IService\DependencyInjection\Tag implements IServiceContainerGeneratorAnnotation
{
  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName)
  {
    $definition->addTag($this->getTagName());
  }
}
