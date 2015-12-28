<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains storage with all data held in memory.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\storage;

use sfStorage;
use sfInitializationException;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\exceptions\NotImplementedException;

/**
 * Storage with all data held in memory. All data will be lost
 * after destruction of the object.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class MemoryStorage extends sfStorage {

  /** @var array(string=>mixed) saved data*/
  private $data;

  /**
   * Initialize this storage instance.
   *
   * Available options: none
   *
   * @param array $options An associative array of options
   *
   * @returns bool true, if initialization completes successfully
   */
  public function initialize($options = array())
  {
    parent::initialize();
    $this->data = array();
    return true;
  }

  /**
   * Reads data from this storage.
   *
   * @param string $key key to the data
   *
   * @returns mixed Data associated with the key
   */
  public function read($key)
  {
    Preconditions::checkIsString($key);
    if (!array_key_exists($key, $this->data)) {
      return null;
    }
    return $this->data[$key];
  }

  /**
   * Writes data to this storage.
   *
   * @param string $key key to the data
   * @param mixed $data
   *
   * @returns void
   */
  public function write($key, $data)
  {
    Preconditions::checkIsString($key);
    $this->data[$key] = $data;
  }

  /**
   * Removes data from this storage.
   *
   * @param string $key key to the data
   *
   * @returns mixed data associated with the key
   */
  public function remove($key)
  {
    Preconditions::checkIsString($key);
    $retval = null;
    if (array_key_exists($key, $this->data)) {
      $retval = $this->data[$key];
      unset($this->data[$key]);
    }
    return $retval;
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
