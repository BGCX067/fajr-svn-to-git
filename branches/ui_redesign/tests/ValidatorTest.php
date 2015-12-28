<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Validator class
 *
 * @package    Fajr
 * @subpackage Tests
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr;

use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{
  public function testIntegers()
  {
    $this->assertTrue(Validator::isInteger("0",null));
    $this->assertTrue(Validator::isInteger("47",null));
    $this->assertFalse(Validator::isInteger("abcd",null));
    $this->assertFalse(Validator::isInteger("12abcd",null));
    $this->assertFalse(Validator::isInteger("47.47",null));
  }

  public function testStrings()
  {
    $this->markTestIncomplete('treba dopisat');
  }
}

?>
