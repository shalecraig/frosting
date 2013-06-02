<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Annotation\ParsingNode;

/**
 *
 * @author Martin
 */
interface IServiceContainerGeneratorAnnotation 
{
  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName);
}

