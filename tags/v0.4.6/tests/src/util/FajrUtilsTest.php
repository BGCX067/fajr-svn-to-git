<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for FajrUtils class
 *
 * @package    Fajr
 * @subpackage Utils
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
namespace fajr\util;

use PHPUnit_Framework_TestCase;
use Exception;
/**
 * @ignore
 */
require_once 'test_include.php';

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

  public function testExtractExceptionInfo()
  {
    $exception = new Exception('testMessage');
    $info = FajrUtils::extractExceptionInfo($exception);
    $this->assertEquals($info['message'], 'testMessage');
    $this->assertEquals($info['file'], $exception->getFile());
    $this->assertEquals($info['line'], $exception->getLine());
    $this->assertEquals($info['code'], $exception->getCode());
    $this->assertEquals($info['previous'], false);
    $trace = $exception->getTrace();
    $infoTrace = $info['trace'];
    $this->assertEquals(count($infoTrace), count($trace));
    foreach ($infoTrace as $item) {
      foreach ($item as $key => $value) {
        if ($key == 'args') {
          $this->assertTrue(is_array($value));
          foreach ($value as $arg) {
            $this->assertTrue(is_string($arg));
          }
        }
        else if ($key == 'line') {
          $this->assertTrue(is_string($value) || is_int($value));
        }
        else {
          $this->assertTrue(is_string($value));
        }
      }
    }
  }

  public function testExtractExceptionInfoWithPrevious()
  {
    $previous = new Exception('previousMessage');
    $exception = new Exception('testMessage', null, $previous);
    $info = FajrUtils::extractExceptionInfo($exception);
    $this->assertTrue(is_array($info['previous']));
    $infoPrevious = $info['previous'];
    $this->assertEquals($infoPrevious['message'], 'previousMessage');
    $this->assertEquals($infoPrevious['file'], $previous->getFile());
    $this->assertEquals($infoPrevious['line'], $previous->getLine());
    $this->assertEquals($infoPrevious['code'], $previous->getCode());
    $this->assertEquals($infoPrevious['previous'], false);
    $trace = $exception->getTrace();
    $infoTrace = $infoPrevious['trace'];
    $this->assertEquals(count($infoTrace), count($trace));
    foreach ($infoTrace as $item) {
      foreach ($item as $key => $value) {
        if ($key == 'args') {
          $this->assertTrue(is_array($value));
          foreach ($value as $arg) {
            $this->assertTrue(is_string($arg));
          }
        }
        else if ($key == 'line') {
          $this->assertTrue(is_string($value) || is_int($value));
        }
        else {
          $this->assertTrue(is_string($value));
        }
      }
    }
  }

}

?>
