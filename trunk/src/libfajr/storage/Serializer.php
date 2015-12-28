<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains helper for data serialization and deserialization.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\storage;

use InvalidArgumentException;
use fajr\libfajr\base\Preconditions;

/**
 * Helps with data serialization and deserialization.
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Serializer
{
  /**
   * Serializes mixed value and returns string.
   *
   * @param mixed $value value to serialize
   *
   * @returns string serialized value
   */
  public static function serialize($value) {
    // Note: unserialize is very unpleasant about return values and can't return 'false'
    // (false means error). Therefore we hack this through array.
    return serialize(array('value' => $value));
  }

  /**
   * Deserialize previously serialized value.
   *
   * @param string $data
   *
   * @returns mixed deserialized value
   */
  public static function deserialize($data) {
    Preconditions::checkIsString($data);
    $result = @unserialize($data);
    if ($result == false || !array_key_exists('value', $result)) {
      throw new InvalidArgumentException("Invalid data to deserialize.");
    }
    return $result['value'];
  }
}
