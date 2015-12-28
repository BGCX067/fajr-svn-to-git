<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;
/**
 * @ignore
 */
require_once 'test_include.php';
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\connection\AIS2ErrorCheckingConnection;
use fajr\libfajr\pub\base\NullTrace;
use Exception;
/**
 * @ignore
 */
class AIS2ErrorCheckingConnectionTest extends PHPUnit_Framework_TestCase
{
  private function newHttpConnection() {
    return $this->getMock('fajr\libfajr\pub\connection\HttpConnection',
                          array('get', 'post', 'addCookie', 'clearCookies', 'close'));
  }

  public function testUiError()
  {
    // TODO(majak): chceme odchytavat testdata/ais2.ui.error.dat?
    // Neviem ci messageBox nebude potom odchytavany aj pri inych chybach (chyba zapisu, ...)
    // Kazdopadne, mozno by to stalo za to to fixnut a potom odchytavat exception v
    // samotnych aplikaciach, treba sa rozhodnut z dizajnerskeho hladiska.
    $this->markTestIncomplete('TODO, vid zdrojak testu.');
  }

  public function testUi2Error()
  {
    $response = file_get_contents(__DIR__.'/testdata/ais2.ui2.error.dat');
    $mockConnection = $this->newHttpConnection();
    $errorConnection = new AIS2ErrorCheckingConnection($mockConnection);

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue($response));
    try {
      $response = $errorConnection->get(new NullTrace(), 'url');
    } catch (Exception $e) {
      $this->assertRegExp("@Aplikácia je na serveri skončená.@", $e->getMessage());
      return;
    }
    $this->fail('Expection not raised!');
  }

  public function testTomcatError()
  {
    $response = file_get_contents(__DIR__.'/testdata/ais2.tomcat.error.dat');
    $mockConnection = $this->newHttpConnection();
    $errorConnection = new AIS2ErrorCheckingConnection($mockConnection);

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue($response));
    try {
      $response = $errorConnection->get(new NullTrace(), 'url');
    } catch (Exception $e) {
      $this->assertRegExp("@Application is not running@", $e->getMessage());
      return;
    }
    $this->fail('Expection not raised!');
  }
}


