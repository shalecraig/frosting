<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Dashboard;

use Frosting\Routing\Router;

/**
 * Description of Dashboard
 *
 * @author Martin
 */
class Dashboard 
{
  /**
   * @var \Frosting\Routing\Router 
   */
  private $routing;
  
  /**
   * @param \Frosting\Routing\Router $routing
   * 
   * @Inject
   */
  public function initialize(Router $routing)
  {
    $this->routing = $routing;
  }
  
  /**
   * @View("dashboard")
   * @Route(name="dashboard", path="/frosting/dashboard")
   */
  public function home()
  {
    return $this->routing->generate("dashboardLoad");
  }
  
  /**
   * @Route(name="dashboardLoad", path="/frosting/dashboard/load")
   */
  public function load()
  {
    
  }
}
