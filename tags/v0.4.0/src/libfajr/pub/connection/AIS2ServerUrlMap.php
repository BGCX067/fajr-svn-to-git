<?php
/**
 * Provides class that holds all server urls at one place.
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

use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\base\Preconditions;

/**
 * A storage class for all ais2 server urls that libfajr will access.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2ServerUrlMap extends DisableEvilCallsObject
{
  /**
   * Protocol to use.
   */
  const PROTOCOL = 'https://';

  /**
   * Map of all paths that ais2 server uses.
   *
   * @var array(string=>string)
   */
  private $paths = array(
      'webui' => 'ais/servlets/WebUIServlet',
      'login' => 'ais/login.do',
      'logout' => 'ais/logout.do',
      'start' => 'ais/start.do',
    );

  /**
   * @var string name of the AIS2 server.
   */
  private $serverName;

  /**
   * @param string $serverName name of the AIS2 server.
   */
  public function __construct($serverName)
  {
    Preconditions::checkIsString($serverName);
    $this->serverName = $serverName;
  }

  /**
   * Return the full url for the specified path.
   *
   * @param string $path path to page
   *
   * @returns string fully qualified url.
   */
  private function _getUrl($path)
  {
    Preconditions::checkNotNull($path, "url path");
    Preconditions::checkIsString($path, "url path");
    return self::PROTOCOL . $this->serverName . '/' . $path;
  }

  /**
   * @returns string url to WebUiServlet
   */
  public function getWebUiServletUrl()
  {
    return $this->_getUrl($this->paths['webui']);
  }

  public function getLoginUrl()
  {
    return $this->_getUrl($this->paths['login']);
  }

  public function getLogoutUrl()
  {
    return $this->_getUrl($this->paths['logout']);
  }

  public function getStartPageUrl()
  {
    return $this->_getUrl($this->paths['start']);
  }
}
