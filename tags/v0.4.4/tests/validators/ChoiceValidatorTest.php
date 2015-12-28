<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for ChoiceValidator class
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
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
class ChoiceValidatorTest extends PHPUnit_Framework_TestCase
{
  private $validator;

  public function setUp()
  {
    $this->validator = new ChoiceValidator(array(1, '2', true, null));
  }


  public function testContains()
  {
    $this->validator->validate(1);
    $this->validator->validate('2');
    $this->validator->validate(true);
    $this->validator->validate(null);
  }

  public function testOther()
  {
    $testcases = array(false, "null", "1", 2, "true");

    foreach ($testcases as $testcase) {
      try {
        $this->validator->validate($testcase);
        $this->assertTrue(false);
      } catch (ValidationException $e) {
      }
    }
  }

}
?>
