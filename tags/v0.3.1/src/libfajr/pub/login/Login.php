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
use fajr\libfajr\pub\connection\AIS2ServerConnection;

interface Login
{
  /**
   * Login user.
   *
   * @param AIS2ServerConnection $connection connection to login with
   *
   * @returns void
   * @throws AIS2LoginException on failure.
   */
  public function login(AIS2ServerConnection $connection);

  /**
   * Logout user
   *
   * @param AIS2ServerConnection $connection connection to logout with
   *
   * @returns void
   * @throws AIS2LoginException on failure.
   */
  public function logout(AIS2ServerConnection $connection);

  /**
   * Check login status
   *
   * @param HttpConnection     $connection
   * @param AIS2ServerInstance $server server to check login status.
   *
   * @returns bool true if user is currently logged in.
   */
  public function isLoggedIn(AIS2ServerConnection $connection);

  /**
   * Try to relogin to ais if neccessary
   *
   * @param HttpConnection     $connection
   * @param AIS2ServerInstance $server server to check and relogin.
   *
   * @throws AIS2LoginException
   */
  public function ais2Relogin(AIS2ServerConnection $connection);
}
