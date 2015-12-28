<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

namespace fajr\libfajr\connection;

use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class RequestStatisticsImplTest extends PHPUnit_Framework_TestCase
{
  private $stats;

  public function setUp()
  {
    $this->stats = new RequestStatisticsImpl();
  }

  public function testInitialValues()
  {
    $this->assertEquals(0, $this->stats->getRequestCount());
    $this->assertEquals(0, $this->stats->getErrorCount());
    $this->assertEquals(0, $this->stats->getDownloadedBytes());
    $this->assertEquals(0, $this->stats->getTotalTime());
  }

  public function testOneRequest()
  {
    $this->stats->addStats(2, 37, 0.42);
    $this->assertEquals(1, $this->stats->getRequestCount());
    $this->assertEquals(2, $this->stats->getErrorCount());
    $this->assertEquals(37, $this->stats->getDownloadedBytes());
    $this->assertEquals(0.42, $this->stats->getTotalTime());
  }

  public function testSeveralRequests()
  {
    $this->stats->addStats(2, 37, 0.42);
    $this->stats->addStats(3, 47, 1.42);
    $this->assertEquals(2, $this->stats->getRequestCount());
    $this->assertEquals(5, $this->stats->getErrorCount());
    $this->assertEquals(84, $this->stats->getDownloadedBytes());
    $this->assertEquals(1.84, $this->stats->getTotalTime(), '', 0.001);

    $this->stats->addStats(0, 0, 0.01);
    $this->assertEquals(3, $this->stats->getRequestCount());
    $this->assertEquals(5, $this->stats->getErrorCount());
    $this->assertEquals(84, $this->stats->getDownloadedBytes());
    $this->assertEquals(1.85, $this->stats->getTotalTime(), '', 0.001);
  }

  public function testClear()
  {
    $this->stats->addStats(2, 37, 0.42);
    $this->stats->clear();
    $this->assertEquals(0, $this->stats->getRequestCount());
    $this->assertEquals(0, $this->stats->getErrorCount());
    $this->assertEquals(0, $this->stats->getDownloadedBytes());
    $this->assertEquals(0, $this->stats->getTotalTime());
  }

}
