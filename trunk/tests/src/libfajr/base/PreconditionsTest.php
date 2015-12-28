<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Preconditions class
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\base;

use Exception;
use fajr\libfajr\base\Preconditions;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class PreconditionsTest extends PHPUnit_Framework_TestCase
{
  public function testNull()
  {
    Preconditions::checkNotNull("simple");
    Preconditions::checkNotNull("simple", "should be non null");
    Preconditions::checkNotNull(0, "zero");
    Preconditions::checkNotNull(0.0, "zero");
    Preconditions::checkNotNull("", "empty string");
    $x = "";
    Preconditions::checkNotNull($x, "empty string");
  }

  public function testNullFail()
  {
    $this->setExpectedException("InvalidArgumentException");
    $x = null;
    Preconditions::checkNotNull($x, "x should'n be null");
  }

  public function testCheck()
  {
    $x = 5;
    Preconditions::check($x > 2, "Argument should be big");
    Preconditions::check($x == $x, "Should equals");
    Preconditions::check($x === $x, "Should be the same");
  }

  public function testCheckFail()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::check(2 > 4, "Plainly wrong");
  }

  public function testString()
  {
    Preconditions::checkIsString("aa", "should be string");
    $x = "bb";
    Preconditions::checkIsString($x, "x should be string");
  }

  public function testStringFail()
  {
    $this->setExpectedException("InvalidArgumentException");
    $x = 5;
    Preconditions::checkIsString($x, "not a string");
  }

  public function testNumber()
  {
    Preconditions::checkIsNumber(1, "should be number");
    Preconditions::checkIsNumber(doubleval(2.0), "should be number");
    Preconditions::checkIsNumber(floatval(3.0), "should be number");
    $x = 1;
    $y = doubleval(2.0);
    $z = floatval(3.0);
    Preconditions::checkIsNumber($x, "x should be number");
    Preconditions::checkIsNumber($y, "y should be number");
    Preconditions::checkIsNumber($z, "z should be number");
  }

  public function testNumberFail()
  {
    $this->setExpectedException("InvalidArgumentException");
    $x = '5';
    Preconditions::checkIsNumber($x, "not a number");
  }

  public function testMatchesPattern()
  {
    Preconditions::checkMatchesPattern("/^aa$/", "aa", "string");
    $x = "bb";
    Preconditions::checkMatchesPattern("/^bb$/", $x, "string");
  }

  public function testMatchesPatternFailOnNonString()
  {
    $this->setExpectedException("InvalidArgumentException");
    $x = 5;
    Preconditions::checkMatchesPattern("/^abc$/", $x, "not a string");
  }

  public function testIsStringAndMatchesFailOnNonMatch()
  {
    $this->setExpectedException("InvalidArgumentException");
    $x = 'def';
    Preconditions::checkMatchesPattern("/^abc$/", $x, "not matching");
  }

  public function testContainsInteger()
  {
    Preconditions::checkContainsInteger("47");
    Preconditions::checkContainsInteger("-47");
    Preconditions::checkContainsInteger(-47);
    Preconditions::checkContainsInteger(-47.0);
    Preconditions::checkContainsInteger(0);
  }

  public function testContainsIntegerFailNull()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger(null);
  }

  public function testContainsIntegeriFailString()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger('string');
  }

  public function testContainsIntegeriFailString2()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger('');
  }

  public function testContainsIntegerFailObject()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger(new Exception());
  }

  public function testContainsIntegerFailBool()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger(false);
  }

  public function testContainsIntegerFailReal()
  {
    $this->setExpectedException("InvalidArgumentException");
    Preconditions::checkContainsInteger(44.3);
  }

}
