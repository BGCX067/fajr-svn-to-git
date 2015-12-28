<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for ConfigUtils class
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use fajr\config\ConfigUtils;
use fajr\validators\ChoiceValidator;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class ConfigUtilsTest extends PHPUnit_Framework_TestCase
{
  
  public function testDefaultValues()
  {
    $desc = array(
        'a' => array('defaultValue' => 0),
        'b' => array('defaultValue' => 'b'),
        'c' => array('defaultValue' => 'not used'));
    $conf = array( 'c' => 'c');
    $result = ConfigUtils::parseAndValidateConfiguration($desc, $conf);
    $this->assertEquals(array('a' => 0, 'b' => 'b', 'c' => 'c'), $result);
  }

  public function testRequiredValue()
  {
    $this->setExpectedException('\Exception');
    $desc = array(
        'a' => array(),
        'b' => array('defaultValue' => 'b'),
        );
    $conf = array('b' => 'new');
    ConfigUtils::parseAndValidateConfiguration($desc, $conf);
  }

  public function testNonexistentOptions()
  {
    $this->setExpectedException('\Exception');
    $desc = array(
        'a' => array('defaultValue' => 'a'),
        );
    $conf = array('b' => 'new');
    ConfigUtils::parseAndValidateConfiguration($desc, $conf);
  }

  public function testValidateOk()
  {
    $desc = array(
        'a' => array('validator' => new ChoiceValidator(array('yes', 'no'))),
        );
    $conf = array('a' => 'yes');
    $result = ConfigUtils::parseAndValidateConfiguration($desc, $conf);
    $this->assertEquals(array('a' => 'yes'), $result);
  }

  public function testValidateFail()
  {
    $this->setExpectedException('\Exception');
    $desc = array(
        'a' => array('validator' => new ChoiceValidator(array('yes', 'no'))),
        );
    $conf = array('a' => 'maybe');
    ConfigUtils::parseAndValidateConfiguration($desc, $conf);
  }

}
