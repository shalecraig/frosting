<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Security;

use Frosting\IService\Security\IAccessControlService;
use Frosting\IService\Security\IAccessControlUser;
use Frosting\BusinessRule\BusinessRuleEngine;
use Frosting\Framework\Frosting;

/**
 * Description of AccessControlManager
 *
 * @author Martin
 */
class AccessControlManager implements IAccessControlService
{
  const BUSINESS_RULE_CONTEXT = "security";
  
  /**
   * @var \Frosting\BusinessRule\BusinessRuleEngine
   */
  private $businessRuleEngine;
  
  /**
   * @var \Frosting\IService\Security\IAccessControlUser 
   */
  private $accessControlUser;
  
  /**
   * @param \Frosting\BusinessRule\BusinessRuleEngine $businessRuleEngine
   * 
   * @Inject
   */
  public function initialize(BusinessRuleEngine $businessRuleEngine)
  {
    $this->businessRuleEngine = $businessRuleEngine;
  }
  
  /**
   * @param \Frosting\IService\Security\IAccessControlUser $accessControlUser
   * 
   * @Injects
   */
  public function setAccessControlUser(IAccessControlUser $accessControlUser)
  {
    $this->accessControlUser = $accessControlUser;
  }
  
  public function checkPermissions(array $permissionRules, IAccessControlUser $accessControlerUser = null) 
  {
    if(is_null($accessControlerUser)) {
      $accessControlerUser = $this->accessControlUser;
    }
    
    return $this->businessRuleEngine->check(
      $permissionRules, 
      self::BUSINESS_RULE_CONTEXT, 
      array($accessControlerUser)
    );
  }  
  
  /**
   * @param mixed $configuration
   * @return IAccessControlService
   */
  public static function factory($configuration = null)
  {
    if(is_null($configuration)) {
      $configuration = __DIR__ . '/frosting.json';
    }
    
    return Frosting::serviceFactory($configuration,self::FROSTING_SERVICE_NAME);
  }
}
