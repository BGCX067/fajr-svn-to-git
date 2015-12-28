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
use fajr\libfajr\pub\connection\RequestStatistics;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\MutableTimer;
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
  /** @var RequestStatisticsImpl */
  private $stats = null;

  /** @var HttpConnection */
  private $delegate = null;

  /** @var MutableTimer */
  private $timer = null;

  /**
   * @var MutableTimer $timer Timer to time requests.
   * Note that timer WILL BE RESETTED.
   */
  function __construct(HttpConnection $delegate, MutableTimer $timer)
  {
    $this->delegate = $delegate;
    $this->timer = $timer;
    $this->stats = new RequestStatisticsImpl();
  }

  public function clear()
  {
    $this->stats->clear();
  }

  public function get(Trace $trace, $url)
  {
    try {
      $this->timer->reset();;
      $result = $this->delegate->get($trace, $url);
      $this->stats->addStats(0, strlen($result), $this->timer->getElapsedTime());
      return $result;
    }
    catch (Exception $e) {
      $this->stats->addStats(1, 0, $this->timer->getElapsedTime());
      throw $e;
    }
  }

  public function post(Trace $trace, $url, $data)
  {
    try {
      $this->timer->reset();
      $result = $this->delegate->post($trace, $url, $data);
      $this->stats->addStats(0, strlen($result), $this->timer->getElapsedTime());
      return $result;
    }
    catch (Exception $e) {
      $this->stats->addStats(1, 0, $this->timer->getElapsedTime());
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

  public function close()
  {
    $this->delegate->close();
  }

  public function getStats()
  {
    return $this->stats;
  }

}
