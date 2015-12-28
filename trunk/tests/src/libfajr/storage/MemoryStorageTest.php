<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\storage;

use \PHPUnit_Framework_TestCase;


/**
 * @ignore
 */
require_once 'test_include.php';
/**
 * @ignore
 */
class MemoryStorageTest extends PHPUnit_Framework_TestCase
{
  private $storage;

  public function setUp()
  {
    $this->storage = new MemoryStorage();
  }

  public function testReadNull()
  {
    $this->assertTrue(null === $this->storage->read('key'));
  }

  public function testWriteRead()
  {
    $this->storage->write('key', 'data');
    $this->assertEquals('data', $this->storage->read('key'));
  }

  public function testMoreWrites()
  {
    $this->storage->write('key', 'data');
    $this->storage->write('key', 'data2');
    $this->storage->write('key2', 'data3');
    $this->assertEquals('data2', $this->storage->read('key'));
    $this->assertEquals('data3', $this->storage->read('key2'));
    $this->assertEquals(null, $this->storage->read('key3'));
  }

  public function testRemove()
  {
    $this->storage->write('key', 'data');
    $this->storage->write('key2', 'data2');
    $this->assertEquals('data', $this->storage->remove('key'));
    $this->assertEquals(null, $this->storage->read('key'));
    $this->assertEquals(null, $this->storage->remove('key'));
    $this->assertEquals(null, $this->storage->read('key'));
    $this->assertEquals('data2', $this->storage->read('key2'));
  }

  public function testWriteAfterRemove()
  {
    $this->storage->write('key', 'data');
    $this->storage->remove('key');
    $this->storage->write('key', 'new');
    $this->assertEquals('new', $this->storage->read('key'));
  }

}
