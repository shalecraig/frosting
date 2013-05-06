<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework\Tests;

use Frosting\Framework\Frosting;
use Frosting\IService\EventDispatcher\IEvent;
use Frosting\IService\EventDispatcher\IEventDispatcherService;

/**
 * Description of FrostingIntegrationTest
 *
 * @author Martin
 */
class FrostingIntegrationTest extends \PHPUnit_Framework_TestCase
{
  private $frosting = null;
  
  public function setUp() {
    $this->frosting = new Frosting(__DIR__ . '/fixtures/integrationTest.json');
  }
  
  public function testListenConnection() 
  {
    $serviceContainer = $this->frosting->getServiceContainer();
    $serviceDispatcher = $serviceContainer->getServiceByName(IEventDispatcherService::FROSTING_SERVICE_NAME);
    $serviceForTest = $serviceContainer->getServiceByName("serviceForTest");
            
    /* @var $serviceDispatcher Frosting\IService\EventDispatcher\IEventDispatcherService */
    $parameter = "namedParameter";
    $event = $serviceDispatcher->dispatch("Test", $this, array("namedParameter"=>$parameter));
    
    $this->assertSame($event, $serviceForTest->event);
    $this->assertSame($this, $serviceForTest->typedParameter);
    $this->assertEquals(10,$serviceForTest->defaultValue);
    $this->assertEquals($parameter, $serviceForTest->namedParameter);
  }
  
  public function testRouteConnection()
  {
    $serviceContainer = $this->frosting->getServiceContainer();
    $serviceRouter = $serviceContainer->getServiceByName('routing');
    $result = $serviceRouter->match('/test');
    $this->assertEquals(
      array(
        'test'=>0,
        '_service'=>array('name'=>'serviceForTest','method'=>'route'),
        '_route'=>'test'
      ), 
      $result
    );
  }
}

class ServiceForTest
{
  public $event = null;
  public $namedParameter = null;
  public $typedParameter = null;
  public $defaultValue = null;
  
  public function reset()
  {
    foreach(get_object_vars($this) as $key => $value) {
      $this->{$key} = $value;
    }
  }
  /**
   * @Listen("Test")
   */
  public function listen(IEvent $event, $namedParameter, FrostingIntegrationTest $typedParameter, $defaultValue = 10)
  {
    foreach(get_defined_vars() as $key => $value) {
      $this->{$key} = $value;
    }
  }
  
  /**
   * @Route(name="test",path="/test",defaults={"test" = 0})
   */
  public function route()
  {
  }
}


