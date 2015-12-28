<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains storage with default data,
 * which can be temporarily (usually per-session)
 * altered.
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
 * TemporarilyModifiableStorage allows storage of temporal modifications.
 * It uses one storage service as permanent storage, which is not modifiable
 * and uses as the source of the data, and the temporary storage
 * (probably session-related) which will store modifications to permanent data.
 * In this way, we can easily maintain big database of default values
 * which are modifieably by any user, but the effect of modifications is temporary.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TemporarilyModifiableStorage extends sfStorage
{
  /** permanent storage option key */
  const PERMANENT = 'permanent_storage';
  /** temporary storage option key */
  const TEMPORARY = 'temporary_storage';

  /** key under which we save our own data to temporary storage */
  const KEY = 'TemporarilyModifiableStorage/changedKeys';

  /** @var array holds keys which were changed */
  private $changedKeys;

  /**
   * Initialize this storage instance.
   *
   * Available options:
   *  * permanent_storage: sfStorage, required
   *  * temporary_storage: sfStorage, required
   *
   * @param array $options An associative array of options
   *
   * @returns bool true, if initialization completes successfully
   */
  public function initialize($options = array())
  {
    parent::initialize($options);
    // $options are now available as $this->options

    if (!isset($this->options[self::PERMANENT])) {
      throw new sfInitializationException('You must provide a "' .
          self::PERMANENT . '" option to FileStorage.');
    }
    Preconditions::check($this->options[self::PERMANENT] instanceof sfStorage,
                        'Permanent storage must be instance of sfStorage');

    if (!isset($this->options[self::TEMPORARY])) {
      throw new sfInitializationException('You must provide a "' .
          self::TEMPORARY . '" option to FileStorage.');
    }
    Preconditions::check($this->options[self::TEMPORARY] instanceof sfStorage,
                        'Temporary storage must be instance of sfStorage');

    if (($initialKeys = $this->options[self::TEMPORARY]->read(self::KEY)) !== null) {
      $this->changedKeys = $initialKeys;
    } else {
      $this->changedKeys = array();
    }
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
    if (array_key_exists($key, $this->changedKeys)) {
      return $this->options[self::TEMPORARY]->read($key);
    }
    return $this->options[self::PERMANENT]->read($key);
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
    $this->options[self::TEMPORARY]->write($key, $data);
    $this->changedKeys[$key] = true;
    $this->options[self::TEMPORARY]->write(self::KEY, $this->changedKeys);
  }

  /**
   * Remove data from storage. Warning: This function removes ONLY temporary
   * modifications! Permanent data won't be erased.
   *
   * @param string $key key to the data
   *
   * @returns mixed TEMPORARY data associated with the key
   */
  public function remove($key)
  {
    Preconditions::checkIsString($key);
    $retval = $this->options[self::TEMPORARY]->read($key);
    $this->options[self::TEMPORARY]->remove($key);
    unset($this->changedKeys[$key]);
    $this->options[self::TEMPORARY]->write(self::KEY, $this->changedKeys);
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
