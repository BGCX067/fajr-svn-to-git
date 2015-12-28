<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides connection to http server utilizing
 * GET and POST requests.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\connection;

use fajr\libfajr\pub\base\Trace;

/**
 * Interface to any http connection with cookie management.
 *
 * If you need only basic request functionality, @see SimpleConnection.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
interface HttpConnection
{
  /**
   * Spravi get request vramci tohto spojenia
   * @param string $url
   */
  public function get(Trace $trace, $url);

  /**
   * Spravi post request vramci tohto spojenia
   * @param string $url
   * @param array  $data asociativne pole dat na poslanie
   */
  public function post(Trace $trace, $url, $data);

  /**
   * Pridá cookie do spojenia
   * @param string  $name      Názov cookie
   * @param string  $value     Hodnota cookie
   * @param int     $expire    Unix timestamp, kedy expiruje (co znamena 0 treba este zistit)
   * @param string  $path      Korenova cesta platnosti cookie. / znamena celu domenu
   * @param string  $domain    Domena, kde cookie plati
   * @param boolean $secure    Ci je potrebne HTTPS na odovzdanie cookie
   * @param boolean $tailmatch Ci mozu vsetky poddomeny dostat tuto cookie
   */
  public function addCookie($name, $value, $expire, $path, $domain,
    $secure=true, $tailmatch=false);

  /**
   * Vymaze vsetky cookie, pripadne aj ich asociovane ulozisko
   */
  public function clearCookies();

  /**
   * Zavrie spojenie.
   * Raz uzavreté spojenie už nemôže byť použité.
   */
  public function close();

}
