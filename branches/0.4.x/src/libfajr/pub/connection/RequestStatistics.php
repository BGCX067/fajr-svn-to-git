<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\connection;

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface RequestStatistics
{
  /**
   * Returns total number of requests
   *
   * @returns int number of requests
   */
  public function getRequestCount();

  /**
   * Returns total number of downloaded bytes
   *
   * @returns int size of downloads
   */
  public function getDownloadedBytes();

  /**
   * Returns total time in seconds requests have taken
   *
   * @returns double time in seconds
   */
  public function getTotalTime();

  /**
   * Returns total number of errors
   *
   * @returns int number of errors
   */
  public function getErrorCount();
}
