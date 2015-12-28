<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
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
use PDO;
use PDOStatement;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class DatabaseStorageSqliteTest extends PHPUnit_Framework_TestCase
{
  protected $backupGlobals = FALSE;
  private $options1;
  private $options2;

  // note: we can't keep storage in private members, because PDO
  // will fail to serialize at the test's end.
  private function getStorage($options) {
    $pdo = new PDO("sqlite::memory:");
    $pdo->exec("CREATE TABLE storage1 (kluc, hodnota)");
    $pdo->exec("CREATE TABLE storage2 (uzivatel, kluc, hodnota)");
    return new DatabaseStorage(array_merge($options, array('pdo' => $pdo)));
  }

  public function setUp()
  {
    if (!in_array('sqlite', PDO::getAvailableDrivers())) {
      $this->markTestSkipped('Sqlite is not available');
    }

    $this->options1 = array(
        'table_name' => 'storage1',
        'key_col' => 'kluc',
        'data_col' => 'hodnota',
        );
    $this->options2 = array(
        'table_name' => 'storage2',
        'key_col' => 'kluc',
        'data_col' => 'hodnota',
        'additional_indexes' => array('uzivatel' => 'ppershing'),
        );
  }

  public function testReadNull()
  {
    $storage = $this->getStorage($this->options1);
    $this->assertSame(null, $storage->read('k1'));
  }

  public function testWriteRead()
  {
    $storage = $this->getStorage($this->options1);
    $storage->write('wr', 47);
    $this->assertSame(47, $storage->read('wr'));
  }

  public function testRewriteRead()
  {
    $storage = $this->getStorage($this->options1);
    $storage->write('rw', 47);
    $storage->write('rw', 42);
    $this->assertSame(42, $storage->read('rw'));
  }

  public function testDeleteRead()
  {
    $storage = $this->getStorage($this->options1);
    $storage->write('del', 47);
    $storage->remove('del');
    $this->assertSame(null, $storage->read('del'));
  }

  public function testMoreKeys()
  {
    $storage = $this->getStorage($this->options1);
    $storage->write('k1', 47);
    $storage->write('k2', "test");
    $storage->write('my/very/complicated/key', array(9));
    $this->assertSame(47, $storage->read('k1'));
    $this->assertSame("test", $storage->read('k2'));
    $this->assertSame(array(9), $storage->read('my/very/complicated/key'));

    $storage->remove('my/very/complicated/key');
    $storage->write('k2', 'test2');

    $this->assertSame(47, $storage->read('k1'));
    $this->assertSame("test2", $storage->read('k2'));
    $this->assertSame(null, $storage->read('my/very/complicated/key'));

  }

}
