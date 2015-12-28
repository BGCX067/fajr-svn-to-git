<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for ais2 Table class.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use \PHPUnit_Framework_TestCase;
use fajr\libfajr\data_manipulation\DataTable;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use fajr\libfajr\pub\base\NullTrace;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class AIS2ApplicationAvailabilityParserTest extends PHPUnit_Framework_TestCase
{
  private $html;

  public function setUp()
  {
    $this->parser = new AIS2ApplicationAvailabilityParser();
  }

  public function testSingleApplicationParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/zoznamAplikaciiSimple.dat');
    $applications = $this->parser->findAllApplications($html);
    $this->assertEquals(array('LZ014'), $applications);
  }

  public function testMultipleApplicationParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/zoznamAplikaciiOriginal.dat');
    $applications = $this->parser->findAllApplications($html);
    $this->assertEquals(array('VSST010', 'VSST178', 'VSST060', 'VSST157'),
                        $applications);
  }
}


