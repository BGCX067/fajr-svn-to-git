<?php
/* {{{
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
namespace fajr\libfajr\connection;

use fajr\libfajr\base\Trace;
use fajr\libfajr\base\Timer;
use \Exception;
/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 */
class StatsConnection implements HttpConnection {

  private $counts = null;
  private $sizes = null;
  private $times = null;
  private $errorCount = 0;
  private $delegate = null;
  private $timer = null;

  /**
   * @var Timer $timer Timer to time requests.
   * Note that timer WILL BE RESETTED.
   */
  function __construct(HttpConnection $delegate, Timer $timer) {
    $this->delegate = $delegate;
    $this->timer = $timer;
    $this->clear();
  }

  public function clear() {
    $this->counts = array('POST'=>0, 'GET'=>0);
    $this->sizes = array('POST'=>0, 'GET'=>0);
    $this->times = array('POST'=>0, 'GET'=>0);
    $this->errorCount = 0;
  }

  public function get(Trace $trace, $url) {
    try {
      $this->timer->reset();;
      $result = $this->delegate->get($trace, $url);
      $this->counts['GET']++;
      $this->sizes['GET'] += strlen($result);
      $this->times['GET'] += $this->timer->getElapsedTime();
      return $result;
    }
    catch (Exception $e) {
      $this->errorCount++;
      $this->counts['GET']++;
      $this->times['GET'] += $this->timer->getElapsedTime();
      throw $e;
    }
  }

  public function post(Trace $trace, $url, $data) {
    try {
      $this->timer->reset();
      $result = $this->delegate->post($trace, $url, $data);
      $this->counts['POST']++;
      $this->sizes['POST'] += strlen($result);
      $this->times['POST'] += $this->timer->getElapsedTime();
      return $result;
    }
    catch (Exception $e) {
      $this->errorCount++;
      $this->counts['POST']++;
      $this->times['POST'] += $this->timer->getElapsedTime();
      throw $e;
    }
  }

  public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
    return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
  }

  public function clearCookies() {
    return $this->delegate->clearCookies();
  }

  public function getCount($type) {
    return $this->counts[$type];
  }

  public function getSize($type) {
    return $this->sizes[$type];
  }

  public function getTime($type) {
    return $this->times[$type];
  }

  public function getTotalCount() {
    $sum = 0;
    foreach ($this->counts as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalSize() {
    $sum = 0;
    foreach ($this->sizes as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalTime() {
    $sum = 0;
    foreach ($this->times as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalErrors() {
    return $this->errorCount;
  }

}
