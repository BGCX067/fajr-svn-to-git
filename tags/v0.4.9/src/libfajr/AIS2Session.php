<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Trieda reprezentujúca session systému (stav prihlásenia, ...)
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr;
use \fajr\libfajr\pub\connection\HttpConnection;
use \fajr\libfajr\pub\login\Login;

/**
 * Trieda reprezentujúca session systému (stav prihlásenia, ...)
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class AIS2Session
{
  private $login = null;

  public function  __construct(Login $login)
  {
    $this->login = $login;
  }

  public function getLogin()
  {
    return $this->login;
  }

}
