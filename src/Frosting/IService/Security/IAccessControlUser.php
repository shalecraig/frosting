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
interface IAccessControlUser 
{
  /**
   * @return array
   */
  public function getPermissions();
}

