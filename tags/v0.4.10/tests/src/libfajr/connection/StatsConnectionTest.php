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
/**
 * @ignore
 */
require_once 'test_include.php';
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\connection\StatsConnection;
use fajr\libfajr\pub\base\NullTrace;
use Exception;
/**
 * @ignore
 */
class StatsConnectionTest extends PHPUnit_Framework_TestCase
{
  private function newConnection()
  {
    return $this->getMock('\fajr\libfajr\pub\connection\HttpConnection');
  }

  public function testStatistics()
  {
    $mockConnection = $this->newConnection();
    $mockTimer = $this->getMock('fajr\libfajr\base\MutableTimer', array('reset', 'getElapsedTime'));
    $statsConnection = new StatsConnection($mockConnection, $mockTimer);
    $mockTimer->expects($this->any())
              ->method('getElapsedTime')
              ->will($this->returnValue(0.1));

    $response0 = "1234";
    $response1 = "123456789";
    $response2 = "1";
    $response4 = "12345";

    $mockConnection->expects($this->at(0))
                   ->method('get')
                   ->will($this->returnValue($response0));
    $mockConnection->expects($this->at(1))
                   ->method('post')
                   ->will($this->returnValue($response1));
    $mockConnection->expects($this->at(2))
                   ->method('get')
                   ->will($this->returnValue($response2));
    $mockConnection->expects($this->at(3))
                   ->method('post')
                   ->will($this->throwException(new Exception()));
    $mockConnection->expects($this->at(4))
                   ->method('get')
                   ->will($this->returnValue($response4));

    $statsConnection->get(new NullTrace(), 'url');
    $statsConnection->post(new NullTrace(), 'url', array());
    $statsConnection->get(new NullTrace(), 'url');
    try {
      $statsConnection->post(new NullTrace(), 'url', array());
    } catch (Exception $e) {
    }
    $statsConnection->get(new NullTrace(), 'url');

    $stats = $statsConnection->getStats();

    $this->assertEquals(1, $stats->getErrorCount());
    $this->assertEquals(5, $stats->getRequestCount());
    $this->assertEquals(19, $stats->getDownloadedBytes());
    $this->assertEquals(0.5, $stats->getTotalTime(), '', 0.01);
  }

}
