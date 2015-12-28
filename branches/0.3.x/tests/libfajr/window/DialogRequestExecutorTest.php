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
  public function testDialogNameParsing()
  {
    $requestBuilder = $this->getMock('fajr\libfajr\window\RequestBuilder');
    $data = new DialogData();
    $connection = $this->getMock('fajr\libfajr\pub\connection\SimpleConnection');
    $executor = new DialogRequestExecutor($requestBuilder, $connection, $data, null, null);

    $response = file_get_contents(__DIR__.'/testdata/vyberTerminuDialogName.dat');
    $name = $executor->parseDialogNameFromResponse($response);
    $this->assertEquals($name, "VSES206_VyberTerminuHodnoteniaDlg1");
  }
}
