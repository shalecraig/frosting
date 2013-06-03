<?php

namespace Frosting\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Frosting\Framework\ConfigurationFileLoader;
use Frosting\Annotation\AnnotationParser;
use Symfony\Component\DependencyInjection\Variable;
use Symfony\Component\Config\Resource\FileResource;

class FrostingCompilerPass implements CompilerPassInterface
{
  private static $defaultConfiguration = array(
    'services' => array(
      'configuration' => array(
        'class' => 'Frosting\Configuration\Configuration'
      )
    ),
    'frosting' => array(
      'annotationNamespaces' => array(__NAMESPACE__)
    )
  );
  
  private $configuration;
  
  /**
   * @var ContainerBuilder
   */
  private $container;
  
  private $loaderFiles;
  
  /**
   * @var \Frosting\Annotation\AnnotationParser 
   */
  private $annotationParser;
  
  public function __construct($configuration)
  {
    $this->configuration = $configuration;
    $fileLoader = new ConfigurationFileLoader();
    $this->configuration = $fileLoader->load($this->configuration);
    $this->loaderFiles = $fileLoader->getLoadedFiles();
    $this->setDefaultConfiguration();
  }
  
  public function getConfiguration()
  {
    return $this->configuration;
  }
  
  public function process(ContainerBuilder $container)
  {
    $this->container = $container;
    
    foreach ($this->loaderFiles as $filePath) {
      $container->addResource(new FileResource($filePath));
    }
    
    $annotationParser = $this->getAnnotationParser();
    $this->prepareDefinition();
    foreach($this->configuration['services'] as $name => $serviceConfiguration) {
      if(isset($serviceConfiguration['disabled']) && $serviceConfiguration['disabled']) {
        continue;
      }
      $definition = $this->container->getDefinition($name);
      $parsingResult = $annotationParser->parse($serviceConfiguration['class']);
      
      $annotations = $parsingResult->getAllAnnotations(array(
        function($annotation) {return $annotation instanceof IServiceContainerGeneratorAnnotation;}
      ));

      foreach($annotations as $parsingNode) {
        $this->addFileResource(get_class($parsingNode['annotation']));
        $generationContext = new GenerationContext($container,$name, $definition, $parsingNode);
        $parsingNode['annotation']->processContainerBuilder($generationContext);
      }
    }
    
    $this->container->getDefinition("configuration")->addArgument(new Variable("this->serviceConfigurations"));
  }
  
  private function setDefaultConfiguration()
  {
    $this->configuration = array_deep_merge(self::$defaultConfiguration, $this->configuration);
  }
  
  /**
   * @return \Frosting\Annotation\AnnotationParser
   */
  private function getAnnotationParser()
  {
    if(is_null($this->annotationParser)) {
      $this->annotationParser = new AnnotationParser();
      foreach($this->configuration['frosting']['annotationNamespaces'] as $namespace) {
        $this->annotationParser->addNamespace($namespace);
      }
    }
    
    return $this->annotationParser;
  }
  
  private function prepareDefinition()
  {
    $definition = new Definition();
    $definition->setClass("Frosting\DependencyInjection\BaseServiceContainer");
    $definition->setFactoryService("service_container");
    $definition->setFactoryMethod("getThis");
    $this->container->setDefinition("serviceContainer", $definition);
    foreach($this->configuration['services'] as $name => $serviceConfiguration) {
      if(isset($serviceConfiguration['disabled']) && $serviceConfiguration['disabled']) {
        continue;
      }
      $this->addFileResource($serviceConfiguration['class']);
      $definition = new Definition();
      $definition->setClass($serviceConfiguration['class']);
      $this->container->setDefinition($name, $definition);
    }
  }
  
  private function addFileResource($class)
  {
    if(!($class instanceof \ReflectionClass)) {
      $class = new \ReflectionClass($class);
    }

    $this->container->addResource(new FileResource($class->getFileName()));
    if($class->getParentClass()) {
      $modificationDates[] = $this->addFileResource($class->getParentClass());
    }
    
    foreach($class->getInterfaces() as $interface) {
      $modificationDates[] = $this->addFileResource($interface);
    }
  }
}