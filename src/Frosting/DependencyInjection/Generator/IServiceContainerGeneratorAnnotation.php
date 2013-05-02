<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection\Generator;

/**
 *
 * @author Martin
 */
interface IServiceContainerGeneratorAnnotation 
{
  public function generateContainer(ContainerGenerator $generator, $serviceName, $methodName);
}

