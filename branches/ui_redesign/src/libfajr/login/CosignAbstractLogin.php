<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\login;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\pub\exceptions\NotImplementedException;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

abstract class CosignAbstractLogin extends DisableEvilCallsObject implements Login
{
  const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
  const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';

  const LOGGED_ALREADY_PATTERN = '@Moja Univerzita Komenského@';
  const IIKS_LOGIN_PATTERN = '@\<title\>IIKS \- Prihlásenie\</title\>@';
  const LOGOUT_PATTERN = '@Portál moja.uniba.sk@';

  public function logout(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $response = $connection->post(new NullTrace(), self::COSIGN_LOGOUT,
        array("verify" => "Odhlásiť",
              "url" => "https://login.uniba.sk/"));
    if (!preg_match(self::IIKS_LOGIN_PATTERN, $response)) {
      throw new LoginException("Unexpected response.");
    }
  }

  public function ais2Relogin(AIS2ServerConnection $serverConnection)
  {
    throw new NotImplementedException();
  }
}
