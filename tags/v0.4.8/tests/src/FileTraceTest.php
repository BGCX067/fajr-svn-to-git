<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for FileTrace class
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\base\SystemTimer;
use fajr\util\File;
use fajr\util\SimpleStringFile;
use fajr\util\TraceUtil;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class FileTraceTest extends PHPUnit_Framework_TestCase
{

  /** @var FileTrace */
  private $trace;

  /** @var SimpleStringFile */
  private $file;

  public function setUp()
  {
    $this->file = new SimpleStringFile();
    $this->trace = new FileTrace(new SystemTimer(), $this->file,
                                 0, '--Header--');
  }

  public function testTlog()
  {
    $this->trace->tlog('<<MESSAGE>>');
    $this->assertRegExp('/<<MESSAGE>>/', $this->file->getString());
  }

  public function testTlogData()
  {
    $this->trace->tlogData('<<MESSAGE>>');
    $this->assertRegExp('/<<MESSAGE>>/', $this->file->getString());
  }

  public function testTlogVariable()
  {
    $this->trace->tlogVariable('<<VARIABLE>>', '<<VALUE>>');
    $this->assertRegExp('/<<VARIABLE>>/', $this->file->getString());
    $this->assertRegExp('/<<VALUE>>/', $this->file->getString());
  }

  public function testAddChild()
  {
    $child = $this->trace->addChild('<<HEADER>>');
    $this->assertNotNull($child);
    $this->assertRegExp('/<<HEADER>>/', $this->file->getString());
  }

}
