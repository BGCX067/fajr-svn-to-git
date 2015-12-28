<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
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
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\pub\exceptions\NotImplementedException;
use fajr\libfajr\util;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * Trieda reprezentujúca prihlasovanie pomocou cosign
 *
 * @author Martin Králik <majak47@gmail.com>
 * @author Martin Sucha <anty.sk@gmail.com>
 */
class CosignPasswordLogin extends CosignAbstractLogin
{
  private $username = null;
  private $krbpwd = null;

  public function __construct($username, $krbpwd)
  {
    assert($username != null);
    assert($krbpwd != null);
    $this->username = $username;
    $this->krbpwd = $krbpwd;
  }

  const COSIGN_ERROR_PATTERN1 =
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>([^<]*)\<\/div\>@';
  const COSIGN_ERROR_PATTERN2 =
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<b\>([^<]*)\<\/b\>@';
  const COSIGN_ERROR_PATTERN3 =
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>\<b\>([^<]*)\<\/b\>@';

  const IIKS_ERROR = '@\<title\>IIKS \- ([^,]*)\<\/title\>@';

  public function login(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $login = $this->username;
    $krbpwd = $this->krbpwd;

    if ($login === null && $krbpwd === null) {
      throw new Exception("S týmto objektom nie je možné sa prihlásiť 2x. " .
          "Meno a heslo boli vymazané pri prvom prihlásení.");
    }

    // Username a password si nebudeme pamatat dlhsie ako treba
    $this->username = null;
    $this->krbpwd = null;
    // TODO(ppershing): why is there this line? Needed for some cookies?
    $this->isLoggedIn($serverConnection);
    $data = $connection->post(new NullTrace(), self::COSIGN_LOGIN,
                              array('ref' => '', 'login'=> $login, 'krbpwd' => $krbpwd));
    if (!preg_match(parent::LOGGED_ALREADY_PATTERN, $data)) {
      if (($reason = util\match(self::COSIGN_ERROR_PATTERN1, $data)) ||
          ($reason = util\match(self::COSIGN_ERROR_PATTERN2, $data)) ||
          ($reason = util\match(self::COSIGN_ERROR_PATTERN3, $data)) ||
          ($reason = util\match(self::IIKS_ERROR, $data))) {
        throw new LoginException('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
      }
      throw new LoginException('Nepodarilo sa prihlásiť, dôvod neznámy.');
    }
    return true;
  }

  public function isLoggedIn(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $data = $connection->get(new NullTrace(), self::COSIGN_LOGIN);
    if (preg_match(self::LOGGED_ALREADY_PATTERN, $data)) {
      return true;
    }
    if (preg_match(self::IIKS_LOGIN_PATTERN, $data)) {
      return false;
    }
    return new LoginException("Unexpected response.");
  }
}
