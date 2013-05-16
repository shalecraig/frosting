<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\BusinessRule\Rule;

use DateTime;

use Frosting\IService\Clock\IClock;

/**
 * Description of DateBefore
 *
 * @author Martin
 */
class DateBefore 
{
  /**
   * @var \Frosting\IService\Clock\IClock
   */
  private $clock;
  
  /**
   * @param \Frosting\IService\Clock\IClock $clock
   * 
   * @Inject
   */
  public function initialize(IClock $clock) 
  {
    $this->clock = $clock;
  }
  
  public function __invoke($date, DateTime $toCompare = null) 
  {
    if(is_null($toCompare)) {
      $toCompareTimestamp = $this->clock->now();
    } else {
      $toCompareTimestamp = $toCompare->getTimestamp();
    }
    return $toCompareTimestamp < $this->clock->strtotime($date);
  }
} 