<?php
/* {{{
 Copyright (c) 2010 Martin Králik
 Copyright (c) 2010 Martin Sucha

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

/**
 * Provides decoration of HttpConnection which
 * checks for generic AIS2 error-response strings.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty@gjh.sk>
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\base\Trace;
use \Exception;
use fajr\libfajr\login\AIS2LoginException;

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
class AIS2ErrorCheckingConnection implements HttpConnection {

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

  function __construct(HttpConnection $delegate) {
    $this->delegate = $delegate;
  }

  /**
   * @throws Exception if error is recognized in response.
   */
  public function get(Trace $trace, $url) {
    return $this->check($trace, $url, $this->delegate->get($trace, $url));
  }

  /**
   * @throws Exception if error is recognized in response.
   */
  public function post(Trace $trace, $url, $data) {
    return $this->check($trace, $url, $this->delegate->post($trace, $url, $data));
  }

  public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
    return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
  }

  public function clearCookies() {
    return $this->delegate->clearCookies();
  }

  private function newException($reason, $url) {
    return new Exception('<b>Nastala chyba pri requeste.</b><br/>Zdôvodnenie od AISu:' .
                         nl2br(hescape($reason)) .
                         '<br/>Požadovaná url: ' . hescape($url));

  }


  private function check(Trace $trace, $url, $response) {
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
      throw new AIS2LoginException("AIS hlási neautorizovaný prístup -
        pravdepodobne vypršala platnosť cookie");
    }
    return $response;
  }
}
