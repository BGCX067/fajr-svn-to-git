<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
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
class TemporarilyModifiableStorageTest extends PHPUnit_Framework_TestCase
{
  private $temp_storage;
  private $perm_storage;
  private $storage;
  private $predefined;

  public function setUp()
  {
    $this->temp_storage = new MemoryStorage();
    $this->perm_storage = new MemoryStorage();
    $this->predefined = array(
        'key1' => 'value1',
        'key2' => 47,
        );
    foreach ($this->predefined as $key => $value) {
      $this->perm_storage->write($key, $value);
    }
    $options = array(
      TemporarilyModifiableStorage::PERMANENT => $this->perm_storage,
      TemporarilyModifiableStorage::TEMPORARY => $this->temp_storage,
      );
    $this->storage = new TemporarilyModifiableStorage($options);
  }

  public function testReadNull()
  {
    $this->assertEquals(null, $this->storage->read('key'));
  }

  public function testRead()
  {
    $this->storage->read('key1', 'value1');
  }

  public function testWriteRead()
  {
    $this->storage->write('key', 'data');
    $this->assertEquals('data', $this->storage->read('key'));
  }

  public function testRewriteRead()
  {
    $this->storage->write('key1', 'data');
    $this->assertEquals('data', $this->storage->read('key1'));
    $this->assertEquals('value1', $this->perm_storage->read('key1'));
  }

  public function testWriteNull()
  {
    $this->storage->write('key1', null);
    $this->assertEquals(null, $this->storage->read('key1'));
    $this->assertEquals(null, $this->temp_storage->read('key1'));
    $this->assertEquals('value1', $this->perm_storage->read('key1'));
  }


  public function testRemove()
  {
    $this->storage->write('key2', 'data');
    $this->assertEquals('data', $this->storage->remove('key2'));
    $this->assertEquals(null, $this->storage->remove('key2'));
    $this->assertEquals(47, $this->storage->read('key2'));
  }

  public function testRemoveNull()
  {
    $this->storage->write('key2', null);
    $this->assertEquals(null, $this->storage->remove('key2'));
    $this->assertEquals(47, $this->storage->read('key2'));
  }

  // TemporarilyModifiableStorage must retain content
  // for another http request!
  public function testWriteReadAnotherInstance()
  {
    $this->storage->write('new', 'new_data');
    $options = array(
      TemporarilyModifiableStorage::PERMANENT => $this->perm_storage,
      TemporarilyModifiableStorage::TEMPORARY => $this->temp_storage,
      );
    $this->storage = new TemporarilyModifiableStorage($options);
    $this->assertEquals('new_data', $this->storage->read('new'));
  }
  public function testWriteRemoveReadAnotherInstance()
  {
    $this->storage->write('key2', 'new_data');
    $options = array(
      TemporarilyModifiableStorage::PERMANENT => $this->perm_storage,
      TemporarilyModifiableStorage::TEMPORARY => $this->temp_storage,
      );
    $this->storage = new TemporarilyModifiableStorage($options);
    $this->assertEquals('new_data', $this->storage->read('key2'));
    $this->storage->remove('key2');

    $this->storage = new TemporarilyModifiableStorage($options);
    $this->assertEquals(47, $this->storage->read('key2'));
  }
}
