<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\BusinessRule\Rule;

/**
 * Description of Debug
 *
 * @author Martin
 */
class Debug 
{
  /**
   * @var \Frosting\Configuration\Configuration
   */
  private $configuration;
  
  public function __invoke()
  {
    return (bool)$this->configuration->get("[configuration][debug]");
  }
}
