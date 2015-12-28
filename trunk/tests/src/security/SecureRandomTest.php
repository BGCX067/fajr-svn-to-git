<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains *basic* tests for SecureRandom
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\security;

use PHPUnit_Framework_TestCase;
use fajr\lib\statistics\PearsonChiSquare;

/**
 * @ignore
 */
require_once 'test_include.php';


class TestProvider implements SecureRandomProvider {
  public function randomBytes($count) {
    return str_repeat('x', $count);
  }
}

/**
 * @ignore
 */
class SecureRandomTest extends PHPUnit_Framework_TestCase
{
  private $provider1;
  private $provider2;

  public function setUp()
  {
    $this->provider1 = $this->getMock('\fajr\security\SecureRandomProvider');
    $this->provider2 = $this->getMock('\fajr\security\SecureRandomProvider');
  }

  public function testConstruct() {
    $random1 = new SecureRandom(array());
    $random2 = new SecureRandom(array($this->provider1));
    $random3 = new SecureRandom(array($this->provider1, $this->provider2));
  }

  public function testBadConstruct() {
    $this->setExpectedException('\Exception');
    $random = new SecureRandom(array('bad'));
  }

  public function testNoProviderRandom() {
    $random = new SecureRandom(array());
    $this->setExpectedException('\Exception');
    $random->randomBytes(10);
  }

  public function testNoProviderCanGenerateRandom() {
    $random = new SecureRandom(array($this->provider1, $this->provider2));
    $this->setExpectedException('\Exception');
    $this->provider1->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(10))
                    ->will($this->returnValue(false));
    $this->provider2->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(10))
                    ->will($this->returnValue(false));

    $random->randomBytes(10);
  }

  public function testFirstProviderCanGenerateRandom() {
    $random = new SecureRandom(array($this->provider1, $this->provider2));
    $this->provider1->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(10))
                    ->will($this->returnValue('0123456789'));
    $this->provider2->expects($this->never())
                    ->method('randomBytes');

    $this->assertEquals('0123456789', $random->randomBytes(10));
  }

  public function testSecondProviderCanGenerateRandom() {
    $random = new SecureRandom(array($this->provider1, $this->provider2));
    $this->provider1->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(4))
                    ->will($this->returnValue(false));
    $this->provider2->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(4))
                    ->will($this->returnValue('abcd'));

    $this->assertEquals('abcd', $random->randomBytes(4));
  }

  public function testProviderReturnsJunk() {
    $random = new SecureRandom(array($this->provider1, $this->provider2));
    $this->setExpectedException('\Exception');
    $this->provider1->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(10))
                    ->will($this->returnValue('junk'));
    $this->setExpectedException('\Exception');
    $random->randomBytes(10);
  }

  public function testProviderReturnsJunk2() {
    $random = new SecureRandom(array($this->provider1, $this->provider2));
    $this->setExpectedException('\Exception');
    $this->provider1->expects($this->once())
                    ->method('randomBytes')
                    ->with($this->equalTo(10))
                    ->will($this->returnValue(null));
    $this->setExpectedException('\Exception');
    $random->randomBytes(10);
  }


  public function testBadParameter() {
    $this->setExpectedException('\Exception');
    $random = new SecureRandom(array(new TestProvider()));
    $random->randomBytes();
  }

  public function testWrongCount() {
    $this->setExpectedException('\Exception');
    $random = new SecureRandom(array(new TestProvider()));
    $random->randomBytes(0);
  }
}
