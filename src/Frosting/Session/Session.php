<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Session;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;
use Frosting\IService\DependencyInjection\ILifeCycleAware;
use Frosting\IService\EventDispatcher\IEventDispatcherService;

/**
 * Description of Session
 *
 * @author Martin
 */
class Session extends BaseSession implements ILifeCycleAware
{
  /**
   * @var \Frosting\IService\EventDispatcher\IEventDispatcherService 
   */
  private $eventDispatcher;
  
  /**
   * @param \Frosting\Session\Session\EventDispatcher $eventDispatcher
   * 
   * @Inject
   */
  public function initiliazlie(IEventDispatcherService $eventDispatcher)
  {
    $this->eventDispatcher = $eventDispatcher;
  }
  
  public function start()
  {
    
  }
  
  public function shutdown()
  {
    $this->eventDispatcher->dispatch('Session.shutdown',$this);
  }
}
