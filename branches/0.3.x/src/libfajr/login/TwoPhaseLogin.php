<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\login;

use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\exceptions\AIS2LoginException;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

class TwoPhaseLogin implements Login
{
  private $cosignLogin = null;
  private $aisLogin = null;

  public function __construct($cosignLogin, $aisLogin)
  {
    $this->cosignLogin = $cosignLogin;
    $this->aisLogin = $aisLogin;
  }

  public function login(AIS2ServerConnection $connection)
  {
    return $this->cosignLogin->login($connection) &&
           $this->aisLogin->login($connection);
  }

  public function isLoggedIn(AIS2ServerConnection $connection)
  {
    return $this->aisLogin->isLoggedIn($connection);
  }

  public function logout(AIS2ServerConnection $connection)
  {
    $exceptions = array();
    try {
      $this->aisLogin->logout($connection);
    } catch (AIS2LoginException $e) {
      $exceptions[] = $e;
    }

    try {
      $this->cosignLogin->logout($connection);
    } catch (AIS2LoginException $e) {
      $exceptions[] = $e;
    }

    // TODO(ppershing): make something similar as umbrella exception in gwt
    if (count($exceptions) != 0) {
      $str = "";
      foreach ($exceptions as $e) {
        $str .= '[' . $e->getMessage() . ']';
      }
      throw new Exception("There were exceptions while logging in: " . $str);
    }
    return true;
  }

  public function ais2Relogin(AIS2ServerConnection $connection)
  {
    return $this->aisLogin->ais2Relogin($connection);
  }
}
