<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Frosting\Security;

use Frosting\IService\Security\IAccessControlUser;

/**
 * Description of SessionAccessControlUser
 *
 * @author Martin
 */
class SessionAccessControlUser implements IAccessControlUser
{
  public function getPermissions() 
  {
    return array('user');
  }
}
