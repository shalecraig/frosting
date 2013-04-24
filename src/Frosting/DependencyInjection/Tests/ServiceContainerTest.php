<?php

namespace Frosting\DependencyInjection\Tests;

use Frosting\DependencyInjection\ServiceContainer;
use Frosting\Annotation\AnnotationParser;
use Frosting\IService\DependencyInjection\Tests\ServiceContainerTest as BaseServiceContainerTest;

class ServiceContainerTest extends BaseServiceContainerTest
{
  protected function getServiceContainer($configuration) 
  {
    $serviceContainer = new ServiceContainer($configuration);
    $serviceContainer->setAnnotationParser(new AnnotationParser());
    return $serviceContainer;
  }
}