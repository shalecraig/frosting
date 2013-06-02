<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\DependencyInjection;

use Frosting\IService\DependencyInjection\Inject as BaseInject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Variable;
use Frosting\Annotation\ParsingNode;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @Annotation
 */
class Inject extends BaseInject implements IServiceContainerGeneratorAnnotation
{  
  private function getParameters($class, $method, $serviceCurrentlyGenerated)
  {
    $reflectionMethod = new \ReflectionMethod($class, $method);
    
    $mapping = $this->getMapping();
    $parameters = array();
    foreach($reflectionMethod->getParameters() as $parameter) {
      /* @var  $parameter \ReflectionParameter */
      if(array_key_exists($parameter->getName(), $mapping)) {
        $serviceName = $mapping[$parameter->getName()];
      } else {
        $serviceName = $parameter->getName();
      }
      
      switch(true) {
        case strpos($serviceName, '@') === 0:
          $parameters[$parameter->getPosition()] = new Variable('this->getServicesByTag("' . substr($serviceName,1) . '")');
          break;
        case strpos($serviceName, '$') === 0:
          if($serviceName == '$') {
            $configuration = '[' . $serviceCurrentlyGenerated . ']';
          } else {
            $configuration = substr($serviceName,1);
          }
          $parameters[$parameter->getPosition()] = new Variable('this->getServiceByName("configuration")->get("' . $configuration . '")');
          break;
        default:
          $parameters[$parameter->getPosition()] = new Reference($serviceName);
          break;
      }
    }
    return $parameters;
  }

  public function processContainerBuilder(ContainerBuilder $generator, Definition $definition, ParsingNode $parsingNode, $serviceName)
  {
    $method = $parsingNode->getContextName();
    $parameters = $this->getParameters($definition->getClass(), $method, $serviceName);
    $definition->addMethodCall($method, $parameters);
  }
}
