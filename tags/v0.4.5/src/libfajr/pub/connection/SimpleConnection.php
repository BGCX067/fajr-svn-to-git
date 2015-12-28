<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\connection;
use fajr\libfajr\pub\base\Trace;

/**
 * Zjednodušená verzia konekcie vhodná pre triedy
 * nezaujímajúce sa o cookies a vyžadujúce automatickú
 * voľbu typu requestu.
 *
 * @author Martin Sucha <anty.sk@gmail.com>
 */
interface SimpleConnection
{
  /**
   * Sprav request na $url. Request je typu GET pokiaľ $post_data sú null.
   *
   * @param string $url       Url requestu.
   * @param array  $post_data @see HttpConnection:post()
   *
   * @returns string
   */
  public function request(Trace $trace, $url, $post_data = null);
}
