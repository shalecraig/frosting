<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

/**
 * @Annotation
 */
class Tag extends \Frosting\IService\DependencyInjection\Tag implements IServiceContainerGeneratorAnnotation
{
  public function processContainerBuilder(GenerationContext $context)
  {
    $context->getServiceDefinition()->addTag($this->getTagName());
  }
}
