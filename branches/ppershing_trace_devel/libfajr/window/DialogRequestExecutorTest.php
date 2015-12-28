<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\base\NullTrace;
use fajr\libfajr\window\DialogRequestExecutor;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\RequestBuilder;
/**
 * @ignore
 */
class DialogRequestExecutorTest extends PHPUnit_Framework_TestCase
{
  public function testDialogNameParsing()
  {
    $requestBuilder = $this->getMock('fajr\libfajr\window\RequestBuilder', array('buildRequestData',
          'getRequestUrl', 'newSerial'));
    $data = new DialogData();
    $executor = new DialogRequestExecutor($data, $requestBuilder, null, null);

    $response = file_get_contents(__DIR__.'/testdata/vyberTerminuDialogName.dat');
    $name = $executor->parseDialogNameFromResponse($response);
    $this->assertEquals($name, "VSES206_VyberTerminuHodnoteniaDlg1");
  }
}


