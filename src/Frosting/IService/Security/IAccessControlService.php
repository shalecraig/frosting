<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\IService\Security;

/**
 *
 * @author Martin
 */
interface IAccessControlService
{
  const FROSTING_SERVICE_NAME = "accessControl";
  
  /**
   * @param array $permissionRules Base on the BusinessRule engine
   * @param IAccessControlUser $accessControlUser
   */
  public function checkPermissions(array $permissionRules, IAccessControlUser $accessControlUser = null);
}
