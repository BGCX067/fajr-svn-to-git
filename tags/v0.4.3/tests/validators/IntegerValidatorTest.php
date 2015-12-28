<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for IntegerValidator class
 *
 * @package    Fajr
 * @subpackage Tests
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\validators;

use PHPUnit_Framework_TestCase;
use fajr\exceptions\ValidationException;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class IntegerValidatorTest extends PHPUnit_Framework_TestCase
{
  private $validator;
  private $uvalidator; // unsigned numbers only

  public function setUp()
  {
    $this->validator = new IntegerValidator();
    $this->uvalidator = new IntegerValidator(false);
  }


  public function testIntegers()
  {
    $this->validator->validate("0");
    $this->validator->validate("1");
    $this->validator->validate("47");
    $this->validator->validate("1234567");
    $this->uvalidator->validate("0");
    $this->uvalidator->validate("1");
    $this->uvalidator->validate("47");
    $this->uvalidator->validate("1234567");
  }

  public function testOther()
  {
    $testcases = array("", "-", "0.0", "--1", "2x", "", "string",
        "1111111111111111111111111111", "-99999999999999999");

    foreach ($testcases as $testcase) {
      try {
        $this->validator->validate($testcase);
        $this->assertTrue(false);
      } catch (ValidationException $e) {
      }
      try {
        $this->uvalidator->validate($testcase);
        $this->assertTrue(false);
      } catch (ValidationException $e) {
      }
    }
  }

  public function testSigned()
  {
    $this->setExpectedException('fajr\exceptions\ValidationException');
    $this->uvalidator->validate('-47');
  }

  public function testSigned2()
  {
    $this->validator->validate('-47');
  }
}
?>
