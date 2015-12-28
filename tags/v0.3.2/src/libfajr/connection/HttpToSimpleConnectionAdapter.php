<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides encapsulated conversion from complex HttpConnection
 * to simple SimpleConnection.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\pub\base\Trace;

/**
 * Adapter that provides SimpleConnection interface
 * encapsulating HttpConnection.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class HttpToSimpleConnectionAdapter implements SimpleConnection
{
  private $connection;

  public function __construct(HttpConnection $connection)
  {
    $this->connection = $connection;
  }

  public function request(Trace $trace, $url, $post_data = null)
  {
    //TODO: assert !=null && !is_array()
    if (is_array($post_data)) {
      return $this->connection->post($trace, $url, $post_data);
    }
    else {
      return $this->connection->get($trace, $url);
    }
  }
}
