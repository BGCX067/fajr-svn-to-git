<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\window;

/**
 * @ignore
 */
require_once 'test_include.php';

use \PHPUnit_Framework_TestCase;
use fajr\libfajr\connection\SimpleConnection;
use fajr\libfajr\window\AbstractScreen;
use fajr\libfajr\base\NullTrace;
/**
 * @ignore
 */
class AIS2AbstractScreenTest extends PHPUnit_Framework_TestCase
{
  public function getScreenStub()
  {
    $connection = $this->getMock('\fajr\libfajr\connection\SimpleConnection', array('request'));
    $screen = $this->getMock('\fajr\libfajr\window\AIS2AbstractScreen',
        array('thisArrayCannotBeEmptyOtherwisePHPUnitGoesCrazy'),
        array(new NullTrace(), $connection, null, null));
    return $screen;
  }

  public function testAppIdParsing()
  {
    $screen = $this->getScreenStub();
    $response = file_get_contents(__DIR__.'/testdata/appid.dat');
    $appId = $screen->parseAppIdFromResponse($response);
    $this->assertEquals(21767494, $appId);
  }

  public function testFormNameParsing()
  {
    $screen = $this->getScreenStub();
    $response = file_get_contents(__DIR__.'/testdata/formName.dat');
    $formName = $screen->parseFormNameFromResponse($response);
    $this->assertEquals("VSES017_StudentZapisneListyDlg0", $formName);
  }

}


