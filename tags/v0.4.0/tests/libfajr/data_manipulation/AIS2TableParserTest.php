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
require_once '../src/libfajr/window/VSES017_administracia_studia/regression/zoznamStudii.table';

/**
 * @ignore
 */
class AIS2TableParserTest extends PHPUnit_Framework_TestCase
{
  private $html;

  public function setUp()
  {
    $this->html = file_get_contents(__DIR__.'/testdata/administraciaStudiaScreen.dat');
    $this->parser = new AIS2TableParser();
  }

  public function testZoznamStudiiTableParsing()
  {
    $definition = \fajr\regression\zoznamStudiiTable::get();
    $table = $this->parser->createTableFromHtml(new NullTrace(), $this->html, 'studiaTable_dataView');
    $data = $table->getData();
    $this->assertEquals(2, count($data));
    $this->assertEquals('mINF', $data[0]['studijnyProgramSkratka']);
    $this->assertEquals('INF', $data[1]['studijnyProgramSkratka']);
    $this->assertEquals($definition, $table->getTableDefinition());
  }

  public function testZapisneListyTableParsing()
  {
    $table = $this->parser->createTableFromHtml(new NullTrace(), $this->html,
        'zapisneListyTable_dataView');
    $data = $table->getData();

    $this->assertEquals(1, count($data));
    $this->assertEquals($data[0]['rokRocnik'], '1');
  }
}


