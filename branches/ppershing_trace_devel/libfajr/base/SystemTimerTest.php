<?php
/**
 * This file contains tests for SystemTimer class
 *
 * @package    Fajr
 * @subpackage TODO
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use \fajr\libfajr\base\SystemTimer;
/**
 * @ignore
 */
class SystemTimerTest extends PHPUnit_Framework_TestCase
{
  private $TEST_TIME = 0.3;
  public function testPassedTime()
  {
    $timer = new SystemTimer();
    usleep($this->TEST_TIME * 1000 * 1000);
    $time = $timer->getElapsedTime();
    $this->assertEquals($this->TEST_TIME, $time, "", 0.01);
  }

  public function testReset()
  {
    $timer = new SystemTimer();
    usleep(2 * $this->TEST_TIME * 1000 * 1000);
    $timer->reset();
    usleep($this->TEST_TIME * 1000 * 1000);
    $time = $timer->getElapsedTime();
    $this->assertEquals($this->TEST_TIME, $time, "", 0.01);

  }

}
