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
use fajr\libfajr\connection\GzipDecompressingConnection;
use fajr\libfajr\base\NullTrace;
/**
 * @ignore
 */
class GzipDecompressingConnectionTest extends PHPUnit_Framework_TestCase
{
  private function newConnection() {
    return $this->getMock('fajr\libfajr\connection\HttpConnection',
                          array('get', 'post', 'addCookie', 'clearCookies'));
  }

  public function testDecompress()
  {
    $response = file_get_contents(__DIR__.'/testdata/fmph.uniba.sk.gzip.dat');
    $mockConnection = $this->newConnection();
    $decompressingConnection =
        new GzipDecompressingConnection($mockConnection, '/tmp');

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue($response));
    $mockConnection->expects($this->once())
                   ->method('post')
                   ->will($this->returnValue($response));

    $response = $decompressingConnection->get(new NullTrace, 'fmph.uniba.sk');
    $this->assertRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);

    $response = $decompressingConnection->post(new NullTrace, 'fmph.uniba.sk', array());
    $this->assertRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);
  }

  public function testNoDecompress()
  {
    $response = file_get_contents(__DIR__.'/testdata/fmph.uniba.sk.zip.dat');
    $mockConnection = $this->newConnection();
    $decompressingConnection =
        new GzipDecompressingConnection($mockConnection, '/tmp');

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue($response));
    $mockConnection->expects($this->once())
                   ->method('post')
                   ->will($this->returnValue($response));

    $response = $decompressingConnection->get(new NullTrace, 'fmph.uniba.sk');
    $this->assertNotRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);
    $this->assertEquals('PK', substr($response, 0, 2));

    $response = $decompressingConnection->post(new NullTrace, 'fmph.uniba.sk', null);
    $this->assertNotRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);
    $this->assertEquals('PK', substr($response, 0, 2));

    // We cannot use $mockConnection for returning more values of same function call
    // @see bug http://www.phpunit.de/ticket/850
    $mockConnection = $this->newConnection();
    $decompressingConnection =
        new GzipDecompressingConnection($mockConnection, '/tmp');
    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue('Some response'));
    $response = $decompressingConnection->get(new NullTrace, 'url');
    $this->assertEquals('Some response', $response);
  }


}


