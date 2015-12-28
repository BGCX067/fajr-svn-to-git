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

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\window\DialogRequestExecutor;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\RequestBuilder;
/**
 * @ignore
 */
class DialogRequestExecutorTest extends PHPUnit_Framework_TestCase
{
  private $requestBuilder;
  private $connection;
  private $executor;

  public function setUp()
  {
    $this->requestBuilder = $this->getMock('fajr\libfajr\window\RequestBuilder');
    $data = new DialogData();
    $this->connection = $this->getMock('fajr\libfajr\pub\connection\SimpleConnection');
    $this->executor = new DialogRequestExecutor($this->requestBuilder,
        $this->connection, $data, null, null);
  }

  public function testDialogNameParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/vyberTerminuDialogName.dat');
    $name = $this->executor->parseDialogNameFromResponse($response);
    $this->assertEquals($name, "VSES206_VyberTerminuHodnoteniaDlg1");
  }

  /** Regression -  issue 77 */
  public function testDialogNameParsingZaporneCisla()
  {
    $response = 'dm().openDialog("VSES017_StudentZapisneListyDlg0","VSES017: Administrácia '.
        'štúdií študenta","VSES017",-8,-8,616,594,1272,664,true,true,true);';

    $name = $this->executor->parseDialogNameFromResponse($response);
    $this->assertEquals($name, "VSES017_StudentZapisneListyDlg0");
  }

}
