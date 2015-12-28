<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\Timer;
use \Exception;

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author Martin Sucha <anty.sk@gmail.com>
 */
class StatsConnection implements HttpConnection
{
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
  function __construct(HttpConnection $delegate, Timer $timer)
  {
    $this->delegate = $delegate;
    $this->timer = $timer;
    $this->clear();
  }

  public function clear()
  {
    $this->counts = array('POST'=>0, 'GET'=>0);
    $this->sizes = array('POST'=>0, 'GET'=>0);
    $this->times = array('POST'=>0, 'GET'=>0);
    $this->errorCount = 0;
  }

  public function get(Trace $trace, $url)
  {
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

  public function post(Trace $trace, $url, $data)
  {
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

  public function addCookie($name, $value, $expire, $path,
                            $domain, $secure = true, $tailmatch = false)
  {
    return $this->delegate->addCookie($name, $value, $expire,
                                      $path, $domain, $secure, $tailmatch);
  }

  public function clearCookies()
  {
    return $this->delegate->clearCookies();
  }

  public function getCount($type)
  {
    return $this->counts[$type];
  }

  public function getSize($type)
  {
    return $this->sizes[$type];
  }

  public function getTime($type)
  {
    return $this->times[$type];
  }

  public function getTotalCount()
  {
    $sum = 0;
    foreach ($this->counts as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalSize()
  {
    $sum = 0;
    foreach ($this->sizes as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalTime()
  {
    $sum = 0;
    foreach ($this->times as $k => $v) {
      $sum += $v;
    }
    return $sum;
  }

  public function getTotalErrors()
  {
    return $this->errorCount;
  }

}
