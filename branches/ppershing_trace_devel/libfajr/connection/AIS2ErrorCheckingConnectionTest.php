<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\connection\HttpConnection;
use fajr\libfajr\connection\AIS2ErrorCheckingConnection;
use fajr\libfajr\base\NullTrace;
use \Exception;
/**
 * @ignore
 */
class AIS2ErrorCheckingConnectionTest extends PHPUnit_Framework_TestCase
{
  private function newHttpConnection() {
    return $this->getMock('fajr\libfajr\connection\HttpConnection',
                          array('get', 'post', 'addCookie', 'clearCookies'));
  }

  public function testUiError()
  {
    // TODO(majak): chceme odchytavat testdata/ais2.ui.error.dat?
    // Neviem ci messageBox nebude potom odchytavany aj pri inych chybach (chyba zapisu, ...)
    // Kazdopadne, mozno by to stalo za to to fixnut a potom odchytavat exception v
    // samotnych aplikaciach, treba sa rozhodnut z dizajnerskeho hladiska.
    $this->fail('TODO, vid zdrojak testu.');
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


