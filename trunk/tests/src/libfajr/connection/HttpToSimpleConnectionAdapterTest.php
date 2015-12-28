<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package Fajr
 * @subpackage Libfajr__Connection
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;
/**
 * @ignore
 */
require_once 'test_include.php';
use PHPUnit_Framework_TestCase;
use fajr\libfajr\connection\HttpToSimpleConnectionAdapter;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\connection\HttpConnection;
/**
 * @ignore
 */
class HttpToSimpleConnectionAdapterTest extends PHPUnit_Framework_TestCase
{
  private function newConnection() {
    return $this->getMock('fajr\libfajr\pub\connection\HttpConnection');
  }

  public function testRequest()
  {
    $mockConnection = $this->newConnection();
    $adapter =
        new HttpToSimpleConnectionAdapter($mockConnection, '/tmp');

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue('get'));

    $mockConnection->expects($this->once())
                   ->method('post')
                   ->will($this->returnValue('post'));

    $response = $adapter->request(new NullTrace, 'fmph.uniba.sk');
    $this->assertEquals('get', $response);

    $response = $adapter->request(new NullTrace, 'fmph.uniba.sk', array());
    $this->assertEquals('post', $response);
  }
}


