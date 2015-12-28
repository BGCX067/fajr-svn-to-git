<?php
/**
 * Provides class wrapping anything related to connection to AIS2 server.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\connection;

use BadFunctionCallException;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\connection\HttpToSimpleConnectionAdapter;
/**
 * Provides class wrapping anything related to connection to AIS2 server.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2ServerConnection extends DisableEvilCallsObject
{
  private $urlMap;
  private $httpConnection;
  private $simpleConnection;

  public function __construct(HttpConnection $connection,
                              AIS2ServerUrlMap $urlMap) {
    $this->urlMap = $urlMap;
    $this->httpConnection = $connection;
    $this->simpleConnection = new HttpToSimpleConnectionAdapter($connection);
  }

  public function getUrlMap()
  {
    return $this->urlMap;
  }

  public function getHttpConnection()
  {
    return $this->httpConnection;
  }

  public function getSimpleConnection()
  {
    return $this->simpleConnection;
  }
}
