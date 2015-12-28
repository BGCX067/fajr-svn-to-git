<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;

/**
 * @ignore
 */
require_once 'test_include.php';

use \PHPUnit_Framework_TestCase;
use fajr\libfajr\window\RequestBuilder;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\pub\base\NullTrace;
/**
 * @ignore
 */
class ScreenRequestExecutorTest extends PHPUnit_Framework_TestCase
{
  private $executor;

  public function setUp()
  {
    $builder = $this->getMock('\fajr\libfajr\window\RequestBuilder',
        array('buildRequestData', 'getRequestUrl', 'newSerial',
              'getAppInitializationUrl'));
    $connection = $this->getMock('\fajr\libfajr\pub\connection\SimpleConnection');
    $this->executor = new ScreenRequestExecutorImpl($builder, $connection);
  }

  public function testAppIdParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/appid.dat');
    $appId = $this->executor->parseAppIdFromResponse($response);
    $this->assertEquals(21767494, $appId);
  }

  public function testFormNameParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/formName.dat');
    $formName = $this->executor->parseFormNameFromResponse($response);
    $this->assertEquals("VSES017_StudentZapisneListyDlg0", $formName);
  }

  /** Regression from issue 77 */
  public function testFormNameParsingZaporneCisla()
  {
    $response = 'dm().openMainDialog("VSES017_StudentZapisneListyDlg0","VSES017: Administrácia '.
        'štúdií študenta","VSES017",-8,-8,616,594,1272,664,true,true,true);';

    $name = $this->executor->parseFormNameFromResponse($response);
    $this->assertEquals($name, "VSES017_StudentZapisneListyDlg0");
  }

}
