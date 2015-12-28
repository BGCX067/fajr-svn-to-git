<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Represents logging into AIS using cosign cookie.
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\login;

use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\pub\login\Login;

/**
 * Trieda reprezentujúca prihlasovanie využívajúce
 * cookie od cosignu.
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class AIS2CosignLogin extends AIS2AbstractLogin {

  /** Pattern of correct sign-out from ais + redirect to cosign. */
  const LOGOUT_OK_PATTERN = '@IIKS - Odhlásenie@';

  private $cosignLogin = null;

  public function __construct(Login $cosignLogin) {
    $this->cosignLogin = $cosignLogin;
  }

  protected function _checkLogoutPattern($response) {
    if (!preg_match(self::LOGOUT_OK_PATTERN, $response)) {
      throw new LoginException("Unexpected response.");
    }
  }

  public function logout(AIS2ServerConnection $connection) {
    $exceptions = array();
    try {
      parent::logout($connection);
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

  /**
   * Note: Login requires that there is valid cosign filter cookie
   * set for $serverConnection's http connection and this cookie
   * should be provided with cosignLogin->login() call
   */
  public function login(AIS2ServerConnection $serverConnection)
  {
    if (!$this->cosignLogin->login($serverConnection)) {
      return false; // nemohli sme sa nalogovat do cosignu
    }

    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLoginUrl());
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      throw new LoginException("Login failed.");
    }
    return true;
  }

  public function ais2Relogin(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLoginUrl());
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      throw new LoginException("Relogin failed. Cosign cookie expired.");
    }
    return true;
  }
}
