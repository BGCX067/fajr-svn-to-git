<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\connection\CurlConnection;
use fajr\libfajr\base\NullTrace;
/**
 * @ignore
 */
class CurlConnectionTest extends PHPUnit_Framework_TestCase
{
  const COOKIE_FILE = '/tmp/curl_connection_test';

  public function setUp()
  {
    @unlink(self::COOKIE_FILE);
  }

  public function tearDown()
  {
    @unlink(self::COOKIE_FILE);
  }

  public function testGet()
  {
    $connection = new CurlConnection(self::COOKIE_FILE);

    $response = $connection->get(new NullTrace, 'fmph.uniba.sk');
    $this->assertRegExp("@<title>Fakulta matematiky, fyziky a informatiky</title>@",
                        $response);
  }

  public function testPostAndCookiePersistence()
  {
    $connection = new CurlConnection(self::COOKIE_FILE);
    // zozen si cookie do cosignu, inak to nefunguje
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // skus sa prihlasit
    $this->assertPostCosignLogin($connection, true);
  }

  private function assertPostCosignLogin($connection, $shouldSucceed) {
    $response = $connection->post(new NullTrace, 'https://login.uniba.sk/cosign.cgi',
                                  array('login' => 'fajr_curl_connection_test_username',
                                        'krbpwd' => 'password',
                                        'submit' => 'Prihlásiť'));

    $this->assertEquals($shouldSucceed,
        preg_match("@fajr_curl_connection_test_username@", $response) > 0);
  }

  public function testAddCookies()
  {
    $connection = new CurlConnection(self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // reset cookie to wrong one
    $connection->addCookie("cosign", "wrong_cookie_value", 0, "/", "login.uniba.sk");
    $this->assertPostCosignLogin($connection, false);
  }

  public function testClearCookies()
  {
    $connection = new CurlConnection(self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    $connection->clearCookies();
    $this->assertPostCosignLogin($connection, false);
  }

  public function testAddAndClearCookies()
  {
    $connection = new CurlConnection(self::COOKIE_FILE);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    // reset cookie to wrong one
    $connection->addCookie("cosign", "wrong_cookie_value", 0, "/", "login.uniba.sk");
    $this->assertPostCosignLogin($connection, false);
    $connection->clearCookies();
    $this->assertPostCosignLogin($connection, false);
    $response = $connection->get(new NullTrace, 'https://login.uniba.sk');
    $this->assertPostCosignLogin($connection, true);
  }
}


