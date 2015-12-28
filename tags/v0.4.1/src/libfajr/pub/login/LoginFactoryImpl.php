<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Login
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 * @author Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\pub\login;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\login\CosignPasswordLogin;
use fajr\libfajr\login\CosignProxyLogin;
use fajr\libfajr\login\CosignCookieLogin;
use fajr\libfajr\login\AIS2PasswordLogin;
use fajr\libfajr\login\AIS2CosignLogin;
use fajr\libfajr\login\NoLogin;
use fajr\libfajr\pub\login\CosignServiceCookie;

class LoginFactoryImpl implements LoginFactory
{
  /**
   * @param CosignServiceCookie $cookie
   * @returns AIS2Login
   */
  public function newLoginUsingCosignCookie(CosignServiceCookie $cookie)
  {
    Preconditions::checkNotNull($cookie, 'cookie');
    return new AIS2CosignLogin(new CosignCookieLogin($cookie));
  }

  /**
   * @param string $username
   * @param string $password
   * @returns AIS2Login
   */
  public function newLoginUsingCosignPassword($username, $password)
  {
    Preconditions::checkIsString($username, 'username');
    Preconditions::checkIsString($password, 'password');
    return new AIS2CosignLogin(new CosignPasswordLogin($username, $password));
  }

  /**
   * @param string $username
   * @param string $password
   * @returns AIS2Login
   */
  public function newLoginUsingPassword($username, $password)
  {
    Preconditions::checkIsString($username, 'username');
    Preconditions::checkIsString($password, 'password');
    return new AIS2PasswordLogin($username, $password);
  }

  /**
   * @param string $proxyDb    Cosign ProxyDB directory
   * @param string $cookieName Name of cosign's proxied cookie
   * @returns Login
   */
  public function newLoginUsingCosignProxy($proxyDb, $cookieName)
  {
    Preconditions::checkIsString($proxyDb, 'proxyDb');
    Preconditions::checkIsString($cookieName, 'cookieName');
    return new AIS2CosignLogin(new CosignProxyLogin($proxyDb, $cookieName));
  }

  /**
   * @returns AIS2Login
   */
  public function newNoLogin()
  {
    return new NoLogin();
  }
}
