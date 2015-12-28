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
 * Helper for PDO mocking.
 * @see http://stackoverflow.com/questions/3138946/mocking-the-pdo-object-using-phpunit
 */
class Mock_PDO {
  private $select, $insert, $delete;

  public function __construct($select, $insert, $delete)
  {
    $this->select = $select;
    $this->delete = $delete;
    $this->insert = $insert;
  }

  public function prepare($query) {
    if (preg_match('@SELECT *hodnota *FROM *tabulka *WHERE.*kluc@', $query)) {
      return $this->select;
    } else if (preg_match('@INSERT@', $query)) {
      return $this->insert;
    } else if (preg_match('@DELETE@', $query)) {
      return $this->delete;
    }
    throw \Exception("wrong query to prepare");
  }
}

class Mock_PDOStatement extends PDOStatement {
  public function __construct()
  {
  }
}

/**
 * @ignore
 */
class DatabaseStorageTest extends PHPUnit_Framework_TestCase
{
  protected $backupGlobals = FALSE;

  private $insert;
  private $delete;
  private $select;
  private $storage;
  private $storage2;


  public function setUp()
  {
    $statementMockMethods = array(
        'bindValue', 'execute', 'errorInfo', 'fetchAll');
    $this->insert = $this->getMock('Mock_PDOStatement', $statementMockMethods);
    $this->delete = $this->getMock('Mock_PDOStatement', $statementMockMethods);
    $this->select = $this->getMock('Mock_PDOStatement', $statementMockMethods);
    $pdo = new Mock_PDO($this->select, $this->insert, $this->delete);

    $options = array(
        'pdo' => $pdo,
        'table_name' => 'tabulka',
        'key_col' => 'kluc',
        'data_col' => 'hodnota',
        );
    $this->storage = new DatabaseStorage($options);

    $options2 = array(
        'pdo' => $pdo,
        'table_name' => 'tabulka',
        'key_col' => 'kluc',
        'data_col' => 'hodnota',
        'additional_indexes' => array('user' => 'ppershing'),
        );
    $this->storage2 = new DatabaseStorage($options2);
  }

  public function testReadNull()
  {
    $this->select->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->select->expects($this->once())
                 ->method('bindValue')
                 ->with('key', 'k');
    $this->select->expects($this->once())
                 ->method('fetchAll')
                 ->will($this->returnValue(array()));

    $this->assertSame(null, $this->storage->read('k'));
  }

  public function testReadError()
  {
    $this->setExpectedException("\sfStorageException");
    $this->select->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(false));
    $this->select->expects($this->any())
                 ->method('errorInfo')
                 ->will($this->returnValue(array('','','')));

    $this->storage->read('k');
  }

  public function testDelete()
  {
    $this->select->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->delete->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->delete->expects($this->once())
                 ->method('bindValue')
                 ->with('key', 'k');

    $this->assertSame(null, $this->storage->remove('k'));
  }

  public function testDeleteError()
  {
    $this->setExpectedException('\sfStorageException');
    $this->select->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->delete->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(false));
    $this->storage->remove('k');
  }

  public function testWrite()
  {
    $this->select->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->delete->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(true));

    $this->insert->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->insert->expects($this->at(0))
                 ->method('bindValue')
                 ->with('key', 'k');
    $this->insert->expects($this->at(1))
                 ->method('bindValue')
                 ->with('data', $this->anything());

    $this->assertSame(null, $this->storage->write('k', 'xx'));
  }

  public function testWriteError()
  {
    $this->setExpectedException('\sfStorageException');

    $this->select->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->delete->expects($this->any())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->insert->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(false));
    $this->storage->write('k', 'xx');
  }

  public function testMoreIndexes()
  {
    $this->select->expects($this->once())
                 ->method('execute')
                 ->will($this->returnValue(true));
    $this->select->expects($this->at(0))
                 ->method('bindValue')
                 ->with($this->anything(), 'ppershing');
    $this->select->expects($this->at(1))
                 ->method('bindValue')
                 ->with('key', 'k');
    $this->select->expects($this->once())
                 ->method('fetchAll')
                 ->will($this->returnValue(array()));

    $this->storage2->read('k');
  }

}
