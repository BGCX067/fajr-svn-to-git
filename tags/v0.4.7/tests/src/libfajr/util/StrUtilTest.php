<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

namespace fajr\libfajr\util;

use \PHPUnit_Framework_TestCase;


/**
 * @ignore
 */
require_once 'test_include.php';
/**
 * @ignore
 */
class StrUtilTest extends PHPUnit_Framework_TestCase
{
  public function testStartsWith()
  {
    $this->assertTrue(StrUtil::startsWith('foobar', 'foo'), 'foobar starts with foo');
    $this->assertTrue(StrUtil::startsWith('foobar', ''), 'foobar starts with empty string');
    $this->assertTrue(StrUtil::startsWith('', ''), 'empty string starts with empty string');
    $this->assertFalse(StrUtil::startsWith('foobar', 'baz'), 'foobar does not start with baz');
    $this->assertFalse(StrUtil::startsWith('foobar', 'bar'), 'foobar does not start with bar');
    $this->assertFalse(StrUtil::startsWith('foobar', 'foobars'), 'foobar does not start with foobars');
  }

  public function testEndsWith()
  {
    $this->assertTrue(StrUtil::endsWith('foobar', 'bar'), 'foobar ends with bar');
    $this->assertTrue(StrUtil::endsWith('foobar', ''), 'foobar ends with empty string');
    $this->assertTrue(StrUtil::endsWith('', ''), 'empty string ends with empty string');
    $this->assertFalse(StrUtil::endsWith('foobar', 'baz'), 'foobar does not end with baz');
    $this->assertFalse(StrUtil::endsWith('foobar', 'foo'), 'foobar does not end with foo');
    $this->assertFalse(StrUtil::endsWith('foobar', 'xfoobar'), 'foobar does not end with xfoobar');
  }

  public function testMatch()
  {
    $this->assertEquals('ab', StrUtil::match('@x(.*)x@', 'yxabxy'));
    $this->assertEquals(false, StrUtil::match('@x(.*)x@', 'yyabxy'));
  }

  public function testMatchAll()
  {
    $this->assertEquals(array("#str rst#", "str", "rst"),
        StrUtil::matchAll('@#([a-z]*) ([a-z]*)#@', 'yxa#str rst#bxy'));
    // named patterns
    $this->assertEquals(array("#str rst#", "str", "rst",
                              "first"=>"str", "second"=>"rst"),
                        StrUtil::matchAll(
                            '@#(?P<first>[a-z]*) (?P<second>[a-z]*)#@',
                            'yxa#str rst#bxy'));

    $this->assertEquals(false, StrUtil::matchAll('@x(.*)x@', 'yyabxy'));
  }


}
