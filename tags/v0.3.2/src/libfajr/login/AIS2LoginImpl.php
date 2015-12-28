<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\login;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class AIS2LoginImpl implements Login
{
  // Note: ais response is in win-1250 charset, so we can't match accents
  const NOT_LOGGED_PATTERN = '@Prihl.senie@';
  const LOGGED_IN_PATTERN = '@\<div class="user-name"\>[^<]@';

  // Cosign response is utf-8.
  const LOGOUT_OK_PATTERN = '@IIKS - Odhlásenie@';

  public function login(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLoginUrl());
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      throw new LoginException("Login failed.");
    }
    return true;
  }

  public function logout(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLogoutUrl());
    if (!preg_match(self::LOGOUT_OK_PATTERN, $data)) {
      throw new LoginException("Unexpected response.");
    }
    return true;
  }

  public function isLoggedIn(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getStartPageUrl());
    if (preg_match(self::NOT_LOGGED_PATTERN, $data)) {
      return false;
    }
    if (preg_match(self::LOGGED_IN_PATTERN, $data)) {
      return true;
    }
    throw new LoginException("Unexpected response.");
  }

  public function ais2Relogin(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLoginUrl());
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      throw new LoginException("Oops, can't relogin. Probably expired cookie.");
    }
    return true;
  }
}
