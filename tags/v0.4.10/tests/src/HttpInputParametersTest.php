<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for HttpInputParameters class
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use Exception;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class HttpInputParametersTest extends PHPUnit_Framework_TestCase
{

  public function setUp()
  {
    $_GET = array();
    $_POST = array();
  }

  public function newStrictValidator($expected_input)
  {
    $validator = $this->getMock('\fajr\validators\InputValidator');
    $validator->expects($this->once())
              ->method('validate')
              ->with($this->equalTo($expected_input));
    return $validator;
  }

  public function newValidator()
  {
    $validator = $this->getMock('\fajr\validators\InputValidator');
    return $validator;
  }

  public function testSimpleGet()
  {
    $_GET = array('param' => 'myvalue');

    $input = new HttpInputParameters(array('param' => $this->newStrictValidator('myvalue')),
                                     array());

    $input->prepare();
    $this->assertEquals('myvalue', $input->getParameter('param'));
    $this->assertEquals(null, $input->getParameter('unknown'));
  }

  public function testSimplePost()
  {
    $_POST = array('pparam' => 'myvalue');

    $input = new HttpInputParameters(array(),
        array('pparam' => $this->newStrictValidator('myvalue')));

    $input->prepare();
    $this->assertEquals('myvalue', $input->getParameter('pparam'));
    $this->assertEquals(null, $input->getParameter('unknown'));
  }

  public function testMerge()
  {
    $_GET = array('param1' => 'myvalue1');
    $_POST = array('param2' => 'myvalue2');

    $input = new HttpInputParameters(
        array('param1' => $this->newStrictValidator('myvalue1')),
        array('param2' => $this->newStrictValidator('myvalue2')));

    $input->prepare();
    $this->assertEquals('myvalue1', $input->getParameter('param1'));
    $this->assertEquals('myvalue2', $input->getParameter('param2'));
  }

  public function testCollision()
  {
    $this->setExpectedException('\Exception');
    $_GET = array('param' => 'myvalue');
    $_POST = array('param' => 'myvalue2');

    $input = new HttpInputParameters(
        array('param' => $this->newValidator()),
        array('param' => $this->newValidator()));

    $input->prepare();
  }

  public function testValidationFailure()
  {
    $this->setExpectedException('\Exception');
    $_GET = array('param' => 'value');
    $validator = $this->getMock('\fajr\validators\InputValidator');
    $validator->expects($this->once())
              ->method('validate')
              ->with($this->equalTo('value'))
              ->will($this->throwException(new Exception()));

    $input = new HttpInputParameters(array('param' => $validator), array());
    $input->prepare();
  }

}
