<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

/**
 *
 * @author Martin
 */
interface IServiceContainerGeneratorAnnotation 
{
  public function processContainerBuilder(GenerationContext $context);
}

