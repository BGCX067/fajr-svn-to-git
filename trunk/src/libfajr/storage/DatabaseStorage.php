<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains database storage.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\storage;

use sfStorage;
use sfInitializationException;
use sfStorageException;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\exceptions\NotImplementedException;
use InvalidArgumentException;
use PDO;

/**
 * DatabaseStorage allows to store data in pdo database.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class DatabaseStorage extends sfStorage
{
  // Note: following regexs are only basics,
  // there is much more stuff to do.
  /** regexp representing valid table names */
  const SAFE_TABLE_NAME = '@^[a-zA-Z][a-zA-Z_0-9]*$@';
  /** regexp representing valid table columns */
  const SAFE_COLUMN_NAME = '@^[a-zA-Z][a-zA-Z_0-9]*$@';

  /** @var PDOStatement */
  private $insertStatement = null;
  /** @var PDOStatement */
  private $selectStatement = null;
  /** @var PDOStatement */
  private $deleteStatement = null;

  /**
   * Initialize this storage instance.
   *
   * Available options:
   *  * table_name: string
   *  * key_col: string column of the keys
   *  * data_col: string column of the data
   *  * additional_indexes: array(col=>value) list of additional indexes to use
   * Warning: NEVER use column names supplied by user. This class have some
   * safety measures against this type of injection, but it is not bulletproof.
   *
   * @param array $options An associative array of options
   *
   * @returns bool true, if initialization completes successfully
   */
  public function initialize($options = array())
  {
    parent::initialize($options);
    // $options are now available as $this->options

    $needed_options = array('table_name', 'key_col', 'data_col');
    foreach ($needed_options as $needed) {
      if (!isset($this->options[$needed])) {
        throw new sfInitializationException('You must provide a "' . $needed .
            '" option to DatabaseStorage.');
      }
    }

    // check required options
    Preconditions::checkIsString($this->options['table_name']);
    Preconditions::checkMatchesPattern(self::SAFE_TABLE_NAME,
        $this->options['table_name']);
    Preconditions::checkIsString($this->options['key_col']);
    Preconditions::checkMatchesPattern(self::SAFE_COLUMN_NAME,
        $this->options['key_col']);
    Preconditions::checkIsString($this->options['data_col']);
    Preconditions::checkMatchesPattern(self::SAFE_COLUMN_NAME,
        $this->options['data_col']);
    // check optional options
    if (isset($options['additional_indexes'])) {
      foreach ($options['additional_indexes'] as $name=>$value) {
        Preconditions::checkIsString($name);
        Preconditions::checkMatchesPattern(self::SAFE_COLUMN_NAME, $name);
      }

    }
  }

  /**
   * PDO does not support dynamic table/column names. Check names
   * we are going to insert raw into the sql statement.
   *
   * Note: This is internal function. Parameters should be checked
   * in constructor and this is just re-check.
   */
  private function assertSafeNames()
  {
    // note: do not use assert() here as it is non-fatal by default and
    // continues execution.
    $ok = true;
    $ok &= preg_match(self::SAFE_TABLE_NAME, $this->options['table_name']);
    $ok &= preg_match(self::SAFE_COLUMN_NAME, $this->options['key_col']);
    $ok &= preg_match(self::SAFE_COLUMN_NAME, $this->options['data_col']);
    if (isset($this->options['additional_indexes'])) {
      foreach ($this->options['additional_indexes'] as $key => $value) {
        $ok &= preg_match(self::SAFE_COLUMN_NAME, $key);
      }
    }
    if (!$ok) {
      throw new Exception("Security violation, unknown reason!");
    }
  }


  /**
   * @returns array additional column names
   */
  private function additionalColumnNames()
  {
    if (!isset($this->options['additional_indexes'])) {
      return array();
    }
    return array_keys($this->options['additional_indexes']);
  }

  /**
   * @returns array additional column bind-names
   */
  private function additionalColumnBinds()
  {
    if (!isset($this->options['additional_indexes'])) {
      return array();
    }
    $result = array();
    for ($i = 0; $i < count($this->options['additional_indexes']); $i++) {
      $result[] = ":additional$i";
    }
    return $result;
  }


  /**
   * Binds additional column bind-names to additional column values
   */
  private function bindAdditional($statement) {
    if (!isset($this->options['additional_indexes'])) {
      return; 
    }

    $i = 0;
    foreach ($this->options['additional_indexes'] as $key=>$value) {
      $statement->bindValue("additional$i", $value);
      $i++;
    }
  }

  /**
   * Create prepared statement for select.
   *
   * @returns PDOStatement
   */
  private function prepareSelect() {
    // this may be security risk, so don't believe constructor and check options twice.
    $this->assertSafeNames();

    $additionalWhere = array_map(
          function($name, $bind) {return $name . '=' . $bind; },
          $this->additionalColumnNames(),
          $this->additionalColumnBinds());
    $where = array_merge(array($this->options['key_col'] . '= :key'),
                         $additionalWhere);


    $optional_where = "";
    $query = 
        "SELECT " . $this->options['data_col'] .
        " FROM " . $this->options['table_name'] .
        " WHERE " . implode(' AND ', $where);
    $statement = $this->options['pdo']->prepare($query);
    if ($statement === false) {
      $info = $this->options['pdo']->errorInfo();
      throw new sfStorageException("Problem while preparing query: " . $info[2]);
    }
    $this->bindAdditional($statement);
    return $statement;
  }

  /**
   * Create prepared statement for insert.
   *
   * @returns PDOStatement
   */
  private function prepareInsert() {
    // this may be security risk, so don't believe constructor and check options twice.
    $this->assertSafeNames();

    $columns = array_merge(
        array($this->options['key_col'],
              $this->options['data_col']),
        $this->additionalColumnNames());
    $binds = array_merge(
        array(':key', ':data'),
        $this->additionalColumnBinds());

    $query = 
        "INSERT INTO " . $this->options['table_name'] .
        "  (" . implode(',', $columns) . ") " .
        " VALUES (" . implode(',', $binds) . ')';
    $statement = $this->options['pdo']->prepare($query);
    if ($statement === false) {
      $info = $this->options['pdo']->errorInfo();
      throw new sfStorageException("Problem while preparing query: " . $info[2]);
    }
    $this->bindAdditional($statement);
    return $statement;
  }

  /**
   * Create prepared statement for delete.
   *
   * @returns PDOStatement
   */
  private function prepareDelete() {
    // this may be security risk, so don't believe constructor and check options twice.
    $this->assertSafeNames();

    $columns = array_merge(
        array($this->options['key_col']),
        $this->additionalColumnNames());
    $binds = array_merge(
        array(':key'),
        $this->additionalColumnBinds());
    $where = array_map(function($name, $bind) { return $name . ' = ' . $bind; },
                       $columns, $binds);

    $query = 
        "DELETE FROM " . $this->options['table_name'] .
        " WHERE " . implode(' AND ', $where);
    $statement = $this->options['pdo']->prepare($query);
    if ($statement === false) {
      $info = $this->options['pdo']->errorInfo();
      throw new sfStorageException("Problem while preparing query: " . $info[2]);
    }
    $this->bindAdditional($statement);
    return $statement;
  }

  /**
   * Reads data from this storage.
   * Reading non-existent key will return null.
   *
   * @param string $key key to the data
   *
   * @returns mixed Data associated with the key
   *
   * @throws sfStorageException on failure
   */
  public function read($key)
  {
    Preconditions::checkIsString($key);
    if ($this->selectStatement === null) {
      $this->selectStatement = $this->prepareSelect();
    }
    $statement = $this->selectStatement;
    $statement->bindValue('key', $key);
    if (!$statement->execute()) {
      $info = $statement->errorInfo();
      throw new sfStorageException("Problem reading key '$key' : ". $info[2]);
    }
    // Warning: do not use $statement->rowCount, as it may not return correct values on SELECTs
    $result = $statement->fetchAll();

    if (count($result) == 0) {
      return null;
    }
    if (count($result) != 1) {
      throw new sfStorageException("There are duplicate entries for key '$key'");
    }
    $data = $result[0][0];

    return Serializer::deserialize($data);
  }

  /**
   * Writes data to storage.
   *
   * Warning: this function is not working transactionally.
   * In case of an error, you may end up with old data deleted and
   * new data never added!
   *
   * @param string $key
   * @param mixed $data
   *
   * @returns void
   */
  public function write($key, $data)
  {
    Preconditions::checkIsString($key);
    $this->remove($key);

    if (!$this->insertStatement) {
      $this->insertStatement = $this->prepareInsert();
    }

    $statement = $this->insertStatement;
    $statement->bindValue('key', $key);
    $statement->bindValue('data', Serializer::serialize($data));
    if (!$statement->execute()) {
      throw new sfStorageException("Problem removing key '$key'");
    }
  }

  /**
   * Removes data associated with key from database.
   *
   * @param string key
   *
   * @returns mixed removed value
   */
  public function remove($key)
  {
    Preconditions::checkIsString($key);
    $ret = $this->read($key);

    if (!$this->deleteStatement) {
      $this->deleteStatement = $this->prepareDelete();
    }

    $statement = $this->deleteStatement;
    $statement->bindValue('key', $key);
    if (!$statement->execute()) {
      throw new sfStorageException("Problem writing into key '$key'");
    }
    return $ret;
  }

  /**
   * Executes the shutdown procedure.
   */
  public function shutdown()
  {
  }

  /**
   * Regenerates id that represents this storage.
   *
   * @param  boolean $destroy Destroy session when regenerating? 
   */
  public function regenerate($destroy = false)
  {
    throw new NotImplementedException();
  }
}
