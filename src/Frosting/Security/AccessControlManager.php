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
   * @param \Frosting\BusinessRule\BusinessRuleEngine $businessRuleEngine
   * 
   * @Inject
   */
  public function initialize(BusinessRuleEngine $businessRuleEnforcer)
  {
    $this->businessRuleEngine = $businessRuleEnforcer;
  }
  
  public function checkPermissions(array $permissionRules, IAccessControlUser $accessControlerUser) 
  {
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
