<?php
/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';
require_once 'Validator.php';

/**
 * @ignore
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{

  // example failujuceho testu
  // TODO(majak): number premenovat na isInteger(), string na isString() a zdokumentovat options
  public function testNumbers()
  {
    $this->assertTrue(Validator::number("0",null));
    $this->assertTrue(Validator::number("47",null));
    $this->assertFalse(Validator::number("abcd",null));
    $this->assertFalse(Validator::number("12abcd",null));
    $this->assertTrue(Validator::number("47.47",null));
  }

}

?>
