<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains read-only storage with all data stored
 * in filesystem.
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

/**
 * FileStorage can be used to retrieve data from filesystem.
 * The data must be in '.dat' files and the format is correct
 * php code (without <?php tags ) which returns result in it's end.
 * Example:
 * <code>
 * return 47;
 * </code>
 * Note however that you can't return "false" because of implementation.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FileStorage extends sfStorage
{
  /**
   * Initialize this storage instance.
   *
   * Available options:
   *  * root_path: string
   *
   * @param array $options An associative array of options
   *
   * @returns bool true, if initialization completes successfully
   */
  public function initialize($options = array())
  {
    parent::initialize($options);
    // $options are now available as $this->options

    if (!isset($this->options['root_path'])) {
      throw new sfInitializationException('You must provide a "root_path" option to FileStorage.');
    }
    Preconditions::checkIsString($this->options['root_path']);
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
    // basic sanity checking so there will be no binary-data tricks
    Preconditions::checkMatchesPattern('@^[a-zA-Z0-9._/]*$@', $key,
        'Key contains invalid characters');

    $path = $this->options['root_path'] . '/' . $key . '.dat';
    if (!file_exists($path)) {
      return null; // empty read
    }
    $content = file_get_contents($path);
    if ($content === false) {
      throw new sfStorageException("Failed to read data for key '$key'");
    }
    $value = @eval($content);
    if ($value === false) {
      $err = error_get_last();
      throw new sfStorageException("Parse error while reading data for key '$key': " .
        htmlspecialchars('at line ' . $err['line'] . ': ' .
                         $err['message'], null, 'UTF-8'));
    }
    if ($value === null) {
      throw new sfStorageException("No returned value while reading data for key '$key'");
    }
    return $value;
  }

  /**
   * Not implemented.
   *
   * @throws NotImplementedException
   */
  public function write($key, $data)
  {
    Preconditions::checkIsString($key);
    throw new NotImplementedException('Won\'t be implemented. ' .
        'Writing php files to filesystem is inherently unsafe.');
  }

  /**
   * Not implemented.
   *
   * @throws NotImplementedException
   */
  public function remove($key)
  {
    Preconditions::checkIsString($key);
    throw new NotImplementedException('Wont\'t be implemented. ' . 
        'Removing files from filesystem is inherently unsafe.');
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
