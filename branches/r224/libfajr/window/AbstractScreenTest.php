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
class AbstractScreenTest extends PHPUnit_Framework_TestCase
{
  public function testAppIdParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/appid.dat');
    $screen = new AIS2AdministraciaStudiaScreen();
    $appId = $screen->parseAppIdFromResponse($response);
    $this->assertEquals($appId, 21767494);
  }

  public function testFormNameParsing()
  {
    $response = file_get_contents(__DIR__.'/testdata/formName.dat');
    $screen = new AIS2AdministraciaStudiaScreen();
    $formName = $screen->parseFormNameFromResponse($response);
    $this->assertEquals($formName, "VSES017_StudentZapisneListyDlg0");
  }

}


