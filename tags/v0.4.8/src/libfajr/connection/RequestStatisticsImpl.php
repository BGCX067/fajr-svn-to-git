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

use fajr\libfajr\pub\connection\RequestStatistics;
use Exception;

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author Martin Sucha <anty.sk@gmail.com>
 */
class RequestStatisticsImpl implements RequestStatistics
{
  private $count = 0;
  private $size = 0;
  private $time = 0;
  private $errorCount = 0;

  /**
   * Add stats for request.
   *
   * @param int $errorCount 
   * @param int $size downloaded bytes
   * @param double $time request time in seconds
   *
   * @returns void
   */
  public function addStats($errorCount, $size, $time)
  {
    $this->count++;
    $this->errorCount += $errorCount;
    $this->size += $size;
    $this->time += $time;
  }

  /**
   * Clear all statistics
   *
   * @returns void
   */
  public function clear()
  {
    $this->count = 0;
    $this->size = 0;
    $this->time = 0;
    $this->errorCount = 0;
  }

  /**
   * Returns total number of requests
   *
   * @returns int number of requests
   */
  public function getRequestCount()
  {
    return $this->count;
  }

  /**
   * Returns total number of downloaded bytes
   *
   * @returns int size of downloads
   */
  public function getDownloadedBytes()
  {
    return $this->size;
  }

  /**
   * Returns total time in seconds requests have taken
   *
   * @returns double time in seconds
   */
  public function getTotalTime()
  {
    return $this->time;
  }

  /**
   * Returns total number of errors
   *
   * @returns int number of errors
   */
  public function getErrorCount()
  {
    return $this->errorCount;
  }
}
