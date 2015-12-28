<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\login;
use fajr\libfajr\login\FakeLogin;
use fajr\libfajr\login\NoLogin;
use fajr\libfajr\pub\login\CosignServiceCookie;

class FakeLoginFactoryImpl implements LoginFactory
{
  /**
   * @returns AIS2Login
   */
  public function newLoginUsingCookie(CosignServiceCookie $cookie)
  {
    $ok = ($cookie->getValue() != "wrong_cookie");
    return new FakeLogin($ok);
  }

  /**
   * @returns AIS2Login
   */
  public function newLoginUsingCosign($username, $password)
  {
    $ok = ($password != "wrong");
    return new FakeLogin($ok);
  }

  /**
   * @param string $proxyDb    Cosign ProxyDB directory
   * @param string $cookieName Name of cosign's proxied cookie
   * @returns Login
   */
  public function newLoginUsingCosignProxy($proxyDb, $cookieName)
  {
    $ok = ($proxyDb != "wrong");
    return new FakeLogin($ok);
  }

  /**
   * @returns AIS2Login
   */
  public function newNoLogin()
  {
    return new NoLogin();
  }
}
