<?php

namespace Frosting\IService\DependencyInjection\Tests;

use \Frosting\IService\DependencyInjection\ServiceDoesNotExistsException;
use \Frosting\IService\DependencyInjection\ServiceDisabledException;

abstract class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{
  /**
   *
   * @var \Frosting\IService\DependencyInjection\IServiceContainer;
   */
  private $serviceContainer;
  
  /**
   * @return \Frosting\IService\DependencyInjection\IServiceContainer
   */
  abstract protected function getServiceContainer($configuration);
  
  /**
   * @return \Frosting\IService\DependencyInjection\IServiceContainer
   */
  private function loadServiceContainer()
  {
    if(is_null($this->serviceContainer)) {
      $this->serviceContainer = $this->getServiceContainer(
        array(
          'test' => array (
              'class' => __NAMESPACE__ . '\TestService',
              'configuration' => 'configuration_string'
           ),
          'disabled' => array('disabled' => true),
          'tag' => array (
              'class' => __NAMESPACE__ . '\Tagged',
           ),
           'itag' => array (
              'class' => __NAMESPACE__ . '\TaggedViaInterface',
           )
        )
      );
      $this->assertInstanceOf('\Frosting\IService\DependencyInjection\IServiceContainer', $this->serviceContainer);
    }
    
    return $this->serviceContainer;
  }
  
  public function testGetServiceByName()
  {
    $serviceTest = $this->loadServiceContainer()->getServiceByName('test');
    
    $this->assertInstanceOf(__NAMESPACE__ . '\TestService', $serviceTest);
    
    $this->assertSame($serviceTest, $this->loadServiceContainer()->getServiceByName('test'));
    
    try {
      $this->loadServiceContainer()->getServiceByName('unknow');
      $this->fail('Must throw a exception when requested a not existing service.');
    } catch (ServiceDoesNotExistsException $e) {
      $this->assertTrue(true);
    }
    
    try {
      $this->loadServiceContainer()->getServiceByName('disabled');
      $this->fail('Must throw a exception when requested a disabled service.');
    } catch (ServiceDisabledException $e) {
      $this->assertTrue(true);
    }
  }
  
  public function testGetServiceNames()
  {
    $serviceNames = $this->loadServiceContainer()->getServiceNames();
    $this->assertEquals(array('test','tag','itag'), $serviceNames);
  }
  
  public function testGetServiceConfiguration()
  {
    $configurationTest = $this->loadServiceContainer()->getServiceConfiguration('test');
    $this->assertEquals('configuration_string', $configurationTest,'Returned service configuration is not good');
    
    $configurationTag = $this->loadServiceContainer()->getServiceConfiguration('tag');
    $this->assertNull($configurationTag,'Returned service configuration should be null');
    
    try {
      $this->serviceContainer->getServiceConfiguration('unknow');
      $this->fail('Must throw a exception when requested configuration of a not existing service.');
    } catch (ServiceDoesNotExistsException $e) {
      $this->assertTrue(true);
    }
  }
  
  public function testGetServiceByTag()
  {
    $iTestServices = $this->loadServiceContainer()->getServicesByTag('ITest');
    
    $this->assertEquals(1, count($iTestServices));
    
    $this->assertInstanceOf(__NAMESPACE__ . '\TaggedViaInterface', $iTestServices[0]);
    
    $testServices = $this->loadServiceContainer()->getServicesByTag('Test');
    
    $this->assertEquals(2, count($testServices));
    
    $this->assertTrue(in_array($iTestServices[0],$testServices));
    
    $this->assertEquals(
      0,
      count($this->loadServiceContainer()->getServicesByTag('Unknow'))
    );
  }
}

if(!class_exists('Frosting\DependencyInjection\Tests\TestService')) {
  class TestService {}
  
  /**
   * @Frosting\IService\DependencyInjection\Tag("Test")
   */
  class Tagged {}
  
  /**
   * @Frosting\IService\DependencyInjection\Tag("ITest")
   */
  interface ITagged {}
  
  /**
   * @Frosting\IService\DependencyInjection\Tag("Test")
   */
  class TaggedViaInterface implements ITagged {}
}