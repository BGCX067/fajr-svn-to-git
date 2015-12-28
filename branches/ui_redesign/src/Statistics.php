<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt starajúci sa o základné štatistiky
 * behu fajr-u
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use libfajr\base\IllegalStateException;
use fajr\libfajr\connection\StatsConnection;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\base\Timer;
use fajr\libfajr\base\SystemTimer;

/**
 * Class for gathering some basic statistics about fajr run
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Statistics
{

  /** var Timer $timer */
  private $timer = null;

  /** var StatsConnection $rawConnection */
  private $rawConnection = null;

  /** var StatsConnection $allConnection */
  private $finalConnection = null;

  /**
   * Construct a new Statistics
   *
   * @param Timer $timer timer to use to measure time
   */
  public function __construct(Timer $timer)
  {
    $this->timer = $timer;
  }

  /**
   * Decorate a raw underlying connection with ability to track
   * statistical information.
   *
   * @param HttpConnection $connection underlying connection to wrap
   * @returns HttpConnection decorated connection
   */
  public function hookRawConnection(HttpConnection $connection)
  {
    if ($this->rawConnection !== null) {
      throw new IllegalStateException('Raw connection already set');
    }
    $this->rawConnection = new StatsConnection($connection, new SystemTimer());
    return $this->rawConnection;
  }

  /**
   * Decorate a final underlying connection with ability to track
   * statistical information.
   *
   * @param HttpConnection $connection underlying connection to wrap
   * @returns HttpConnection decorated connection
   */
  public function hookFinalConnection(HttpConnection $connection)
  {
    if ($this->finalConnection !== null) {
      throw new IllegalStateException('Final connection already set');
    }
    $this->finalConnection = new StatsConnection($connection, new SystemTimer());
    return $this->finalConnection;
  }

  /**
   * @returns int total number of executed requests
   */
  public function getRequestCount()
  {
    return $this->finalConnection->getTotalCount();
  }

  /**
   * @returns int total number of downloaded and processed bytes
   */
  public function getDecodedByteCount()
  {
    return $this->finalConnection->getTotalSize();
  }

  /**
   * @returns int total number of downloaded unprocessed bytes
   */
  public function getDownloadedByteCount()
  {
    return $this->rawConnection->getTotalSize();
  }

  /**
   * @returns float (approximate) total execution time in seconds
   */
  public function getTotalTime()
  {
    return $this->timer->getElapsedTime();
  }

  /**
   * @returns float total network IO time spent in seconds
   */
  public function getTotalRequestTime()
  {
    return $this->finalConnection->getTotalTime();
  }

}