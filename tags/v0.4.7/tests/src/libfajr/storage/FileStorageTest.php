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
class FileStorageTest extends PHPUnit_Framework_TestCase
{
  private $storage;

  public function setUp()
  {
    $options = array('root_path' => __DIR__ . '/testdata');
    $this->storage = new FileStorage($options);
  }

  public function testReadNull()
  {
    $this->assertEquals(null, $this->storage->read('null'));
  }

  public function testReadData()
  {
    $this->assertEquals('data', $this->storage->read('key'));
    $this->assertEquals(47, $this->storage->read('47'));
  }

  public function testReadSubdir()
  {
    $this->assertEquals('my_key', $this->storage->read('dir1/dir2/my_key'));
  }

  public function testBadRead()
  {
    $this->setExpectedException('\sfStorageException');
    $this->storage->read('bad');
  }

  // do not allow to write to file, as it is a security risk.
  public function testNoWrite()
  {
    $this->setExpectedException(
        '\fajr\libfajr\pub\exceptions\NotImplementedException');
    $this->storage->write('key', 'data');
  }

  // do not allow to remove a file, as it is a security risk.
  public function testNoRemove()
  {
    $this->setExpectedException(
        'fajr\libfajr\pub\exceptions\NotImplementedException');
    $this->storage->remove('key');
  }
  
}
