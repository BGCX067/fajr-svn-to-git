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
class AIS2AbstractDialogTest extends PHPUnit_Framework_TestCase
{
  public function testDialogNameParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/vyberTerminuDialogName.dat');
    $screen = new AIS2AbstractDialog(null, null, null, null);
    $name = $screen->parseDialogNameFromResponse($response);
    $this->assertEquals($name, "VSES206_VyberTerminuHodnoteniaDlg1");
  }
}


