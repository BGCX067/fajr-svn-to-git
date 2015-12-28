<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides decoration of HttpConnection which
 * checks for generic AIS2 error-response strings.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use Exception;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\util\StrUtil;

/**
 * HttpConnection which checks for generic
 * AIS2 error-response strings and throws Exception if found.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty@gjh.sk>
 * @author     Martin Kralik <majak47@gmail.com>
 */

class AIS2ErrorCheckingConnection implements HttpConnection
{
  private $delegate = null;

  /**
   * Generic error in response.
   */
  const INTERNAL_ERROR_PATTERN = '@^function main\(\) { (?:alert|webui\.onAppClosedOnServer)\(\'([^\']*)\'\);? }$@m';

  /**
   * AIS2 java stacktrace in response.
   */
  const APACHE_ERROR_PATTERN = '@Apache Tomcat.*<pre>([^<]*)</pre>@m';

  /**
   * AIS2 unauthorized.
   */
  const UNAUTHORIZED = "@Neautorizovaný prístup!@";

  function __construct(HttpConnection $delegate)
  {
    $this->delegate = $delegate;
  }

  /**
   * @throws Exception if error is recognized in response.
   */
  public function get(Trace $trace, $url)
  {
    return $this->check($trace, $url, $this->delegate->get($trace, $url));
  }

  /**
   * @throws Exception if error is recognized in response.
   */
  public function post(Trace $trace, $url, $data)
  {
    return $this->check($trace, $url, $this->delegate->post($trace, $url, $data));
  }

  public function addCookie($name, $value, $expire, $path, $domain,
                            $secure = true, $tailmatch = false)
  {
    return $this->delegate->addCookie($name, $value, $expire, $path,
                                      $domain, $secure, $tailmatch);
  }

  public function clearCookies()
  {
    return $this->delegate->clearCookies();
  }

  public function close()
  {
    $this->delegate->close();
  }

  private function newException($reason, $url)
  {
    return new Exception('<b>Nastala chyba pri requeste.</b><br/>Zdôvodnenie od AISu:' .
                         nl2br(StrUtil::hescape($reason)) .
                         '<br/>Požadovaná url: ' . StrUtil::hescape($url));

  }

  private function check(Trace $trace, $url, $response)
  {
    $matches = array();
    if (preg_match(self::INTERNAL_ERROR_PATTERN, $response, $matches)) {
      $trace->tlog("Expection encountered");
      throw $this->newException($matches[1], $url);
    }
    if (preg_match(self::APACHE_ERROR_PATTERN, $response, $matches)) {
      $trace->tlog("Expection encountered");
      throw $this->newException($matches[1], $url);
    }
    if (preg_match(self::UNAUTHORIZED, $response)) {
      $trace->tlog("Exception encountered");
      throw new LoginException("AIS hlási neautorizovaný prístup - má užívateľ prístup k aplikácii?");
    }
    return $response;
  }
}
