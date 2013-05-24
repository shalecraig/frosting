<?php

namespace Frosting\Security\Tests;

use Frosting\Session\ServiceBounder;

class SessionServiceBounderTest extends \PHPUnit_Framework_TestCase
{
  /**
   *
   * @var Frosting\Session\SessionServiceBounder
   */
  private $sessionServiceBounder;
  
  public function setUp() 
  {
    $this->sessionServiceBounder = new ServiceBounder();
  }
  
  private function getNewSession()
  {
    return new \Symfony\Component\HttpFoundation\Session\Session(
      new \Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()
    );
  }
  
  public function test()
  {
    $properties = array(
      'private',
      'protected',
      'public'
    );
    
    $session = $this->getNewSession();
    $serviceToBoundTo = new ServiceToBoundTo();
    $this->sessionServiceBounder->setBindingAttributes(
      'test', 
      $properties
    );
    
    $this->sessionServiceBounder->setSession($session);
    $this->sessionServiceBounder->restoreFromSession($serviceToBoundTo, 'test');
    
    foreach($properties as $name) {
      $this->assertEquals($name . 'Default', $serviceToBoundTo->getProperty($name));
    }
    
    foreach($properties as $name) {
      $serviceToBoundTo->setProperty($name, $name . 'Value');
    }
    
    $this->sessionServiceBounder->setToSession($serviceToBoundTo, 'test');
    
    $serviceToBoundTo2 = new ServiceToBoundTo();
    
    foreach($properties as $name) {
      $this->assertEquals($name . 'Default', $serviceToBoundTo2->getProperty($name));
    }
   
    $this->sessionServiceBounder->restoreFromSession($serviceToBoundTo2, 'test');
    
    foreach($properties as $name) {
      $this->assertEquals($name . 'Value', $serviceToBoundTo2->getProperty($name));
    }
  }
}

class ServiceToBoundTo
{
  private $private = 'privateDefault';
  
  protected $protected = 'protectedDefault';
  
  public $public = 'publicDefault';
  
  public function getProperty($name)
  {
    return $this->{$name};
  }
  
  public function setProperty($name, $value)
  {
    $this->{$name} = $value;
  }
}