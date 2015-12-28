<?php
/**
 * This file contains tests for FajrUtils class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Martin Sucha
 */
/* {{{
  Copyright (c) 2010 Martin Sucha

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.
  }}} */

/**
 * @ignore
 */
require_once 'test_include.php';
require_once 'FajrUtils.php';

/**
 * @ignore
 */
class FajrUtilsTest extends PHPUnit_Framework_TestCase
{

  public function testJoinPath()
  {
    $DS = DIRECTORY_SEPARATOR;
    $this->assertEquals(FajrUtils::joinPath($DS, $DS), $DS, '/,/');
    $this->assertEquals(FajrUtils::joinPath('foo', $DS), 'foo', 'foo,/');
    $this->assertEquals(FajrUtils::joinPath($DS, 'foo'), $DS.'foo', '/,foo');
    $this->assertEquals(FajrUtils::joinPath($DS.'foo'.$DS, 'bar'), $DS.'foo'.$DS.'bar', '/foo/,bar');
    $this->assertEquals(FajrUtils::joinPath('foo', 'bar'), 'foo'.$DS.'bar', 'foo,bar');
    $this->assertEquals(FajrUtils::joinPath('foo'.$DS, $DS.'bar'), 'foo'.$DS.'bar', 'foo/,/bar');
    $this->assertEquals(FajrUtils::joinPath($DS.'foo'.$DS, $DS.'bar'.$DS), $DS.'foo'.$DS.'bar'.$DS, '/foo/,/bar/');
    $this->assertEquals(FajrUtils::joinPath('', ''), '', ',');
    $this->assertEquals(FajrUtils::joinPath($DS, 'foo', $DS, 'bar',$DS.'baz'), $DS.'foo'.$DS.'bar'.$DS.'baz', '/,foo,/,bar,/baz');
  }

  public function testIsAbsolutePath()
  {
    $this->assertTrue(FajrUtils::isAbsolutePath('/'), 'Unix filesystem root is absolute');
    $this->assertTrue(FajrUtils::isAbsolutePath('/foo'), '/foo is absolute');
    $this->assertTrue(FajrUtils::isAbsolutePath('/foo/bar/'), '/foo/bar is absolute');
    $this->assertTrue(FajrUtils::isAbsolutePath('C:\\'), 'C:\\ is absolute');
    $this->assertTrue(FajrUtils::isAbsolutePath('\\\\servername\\folder'), '\\\\servername\\folder UNC path is absolute');
    $this->assertFalse(FajrUtils::isAbsolutePath('foo'), 'foo is relative');
    $this->assertFalse(FajrUtils::isAbsolutePath('./'), './ is relative');
  }

  public function testStartsWith()
  {
    $this->assertTrue(FajrUtils::startsWith('foobar', 'foo'), 'foobar starts with foo');
    $this->assertTrue(FajrUtils::startsWith('foobar', ''), 'foobar starts with empty string');
    $this->assertTrue(FajrUtils::startsWith('', ''), 'empty string starts with empty string');
    $this->assertFalse(FajrUtils::startsWith('foobar', 'baz'), 'foobar does not start with baz');
    $this->assertFalse(FajrUtils::startsWith('foobar', 'bar'), 'foobar does not start with bar');
    $this->assertFalse(FajrUtils::startsWith('foobar', 'foobars'), 'foobar does not start with foobars');
  }

  public function testEndsWith()
  {
    $this->assertTrue(FajrUtils::endsWith('foobar', 'bar'), 'foobar ends with bar');
    $this->assertTrue(FajrUtils::endsWith('foobar', ''), 'foobar ends with empty string');
    $this->assertTrue(FajrUtils::endsWith('', ''), 'empty string ends with empty string');
    $this->assertFalse(FajrUtils::endsWith('foobar', 'baz'), 'foobar does not end with baz');
    $this->assertFalse(FajrUtils::endsWith('foobar', 'foo'), 'foobar does not end with foo');
    $this->assertFalse(FajrUtils::endsWith('foobar', 'xfoobar'), 'foobar does not end with xfoobar');
  }

  public function testFormatPlural()
  {
    $this->assertEquals(FajrUtils::formatPlural(0, 'ok %d', 'failed one %d', 'failed 2-4 %d', 'failed other %d'), 'ok 0');
    $this->assertEquals(FajrUtils::formatPlural(1, 'failed zero %d', 'ok %d', 'failed 2-4 %d', 'failed other %d'), 'ok 1');
    $this->assertEquals(FajrUtils::formatPlural(2, 'failed zero %d', 'failed one %d', 'ok %d', 'failed other %d'), 'ok 2');
    $this->assertEquals(FajrUtils::formatPlural(3, 'failed zero %d', 'failed one %d', 'ok %d', 'failed other %d'), 'ok 3');
    $this->assertEquals(FajrUtils::formatPlural(4, 'failed zero %d', 'failed one %d', 'ok %d', 'failed other %d'), 'ok 4');
    $this->assertEquals(FajrUtils::formatPlural(5, 'failed zero %d', 'failed one %d', 'failed 2-4 %d', 'ok %d'), 'ok 5');
    $this->assertEquals(FajrUtils::formatPlural(10, 'failed zero %d', 'failed one %d', 'failed 2-4 %d', 'ok %d'), 'ok 10');
  }

}

?>
