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
class AdministraciaStudiaScreenTest extends PHPUnit_Framework_TestCase
{
  public function testIdFromZapisnyListIndexParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/idFromZapisnyList.dat');
    $screen = new AIS2AdministraciaStudiaScreen();
    $data = $screen->parseIdFromZapisnyListIndexFromResponse($response);
    $expected = array("idZapisnyList" => 138174, "idStudium" => "53043");
    $this->assertEquals($expected, $data);
  }

}


