<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Framework;

use Frosting\IService\Clock\IClock;

/**
 * Description of Clock
 *
 * @author Martin
 */
class Clock implements IClock
{
  private $now;
  
  /**
   * @param array $configuration
   * 
   * @Inject(configuration="$")
   */
  public function setConfiguration(array $configuration)
  {
    if(isset($configuration['now'])) {
      $this->setNow($configuration['now']);
    }
  }
  
  public function setNow($time)
  {
    $this->now = strtotime($time);
  }
  
  public function alter($time) 
  {
    $this->now = strtotime($time,$this->now());
  }
  
  public function now($format = "U")
  {
    return date($format,is_null($this->now) ? time() : $this->now);
  }
  
  public function getTimestampDifference() 
  {
    return time() - $this->now();
  }
  
  public function strtotime($time)
  {
    if(strtotime($time,$this->now) == strtotime($time)) {
      return strtotime($time);
    }
    
    return strtotime($time) - $this->getTimestampDifference();
  }
}
