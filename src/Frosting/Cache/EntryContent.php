<?php

namespace Frosting\Cache;

/**
 * The content of an entry to store
 */
class EntryContent
{
  /**
   * @var mixed
   */
  private $value;
  
  /**
   * @var integer timestamp
   */
  private $updateTimestamp;
  
  /**
   * @var integer 0 = no limit
   */
  private $lifetime;
  
  /**
   * Mixed data that storage use to add remove flag
   * 
   * @var array 
   */
  private $data = array();
  
  /**
   * @param mixed $value
   * @param integer $updateTimestamp Timestamp
   * @param integer $lifetime
   */
  public function __construct($value, $updateTimestamp, $lifetime)
  {
    $this->value = $value;
    $this->updateTimestamp = $updateTimestamp;
    $this->lifetime = $lifetime;
  }
  
  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @return integer
   */
  public function getUpdateTimestamp()
  {
    return $this->updateTimestamp;
  }
      
  /**
   * @return integer
   */
  public function getLifetime()
  {
    return $this->lifetime;
  }
  
  /**
   * @param type $name
   * @param type $value 
   */
  public function set($name,$value)
  {
    $this->data[$name] = $value;
  }
  
  public function isExpired($currentTime)
  {
    return $this->getLifetime() && $this->getUpdateTimestamp() + $this->getLifetime() < $currentTime;
  }
  
  /**
   * @param type $name
   * @param type $default
   * @return type 
   */
  public function get($name,$default = null)
  {
    return array_key_exists($name, $this->data) ? $this->data[$name] : $default;
  }
}