<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Routing;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Frosting\Framework\Frosting;

/**
 * Description of Router
 *
 * @author Martin
 */
class Router
{
  /**
   * @var RouteCollection
   */
  private $routeCollection;
  
  /**
   * @var RequestContext 
   */
  private $context;
  
  /**
   * @var UrlMatcher
   */
  private $urlMatcher;
  
  public function __construct() 
  {
    $this->routeCollection = new RouteCollection();
    $this->context = new RequestContext();
    $this->urlMatcher = new UrlMatcher($this->routeCollection, $this->context);
  }
  
  public function addRoute($name, $path, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array())
  {
    $route = new Route($path,$defaults,$requirements,$options,$host,$schemes,$methods);
    $this->routeCollection->add($name, $route);
  }
  
  public function removeRoute($name)
  {
    $this->routeCollection->remove($name);
  }
  
  public function match($pathinfo)
  {
    return $this->urlMatcher->match($pathinfo);
  }
  
  public function generate()
  {
    
  }
  
  /**
   * @param mixed $configuration
   * @return IObjectFactoryService
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,'routing');
  }
}