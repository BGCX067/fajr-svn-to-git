<?php
/**
 *
 * @package libfajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class TableTest extends PHPUnit_Framework_TestCase
{
  public function testZoznamStudiiTableParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/tableZoznamStudii.dat');
    $definition = AIS2AdministraciaStudiaScreen::get_tabulka_zoznam_studii();
    $table = new AIS2Table($definition, $html);
    $data = $table->getData();
    $this->assertEquals(2, count($data));
    $this->assertEquals('mINF', $data[0]['skratka']);
    $this->assertEquals('INF', $data[1]['skratka']);
  }

  public function testHodnoteniaTableParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/tableHodnotenia.dat');
    $definition = AIS2HodnoteniaPriemeryScreen::get_tabulka_hodnotenia();
    $table = new AIS2Table($definition, $html);
    $data = $table->getData();
    // syntax je zavadzajuca: GreaterThan(expected, actual)
    $this->assertGreaterThan(1, count($data));
    $this->assertEquals($data[0]['znamka'], 'A');
  }
}


