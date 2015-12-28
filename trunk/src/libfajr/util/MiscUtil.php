<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace fajr\libfajr\util;

/**
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */

class MiscUtil
{
  /**
   * Function which removes all integer-indexed entries from array.
   * Useful for removing unnamed matches after matchAll with named patterns.
   * @param array() $data Array containing string&integer indexed values 
   * @returns array() Data wihout any integer-indexed values
   */
  public static function removeIntegerIndexesFromArray($data)
  {
    foreach (array_keys($data) as $key) {
      if (is_numeric($key)) {
        unset($data[$key]);
      }
    }
    return $data;
  }
  
  public static function random()
  {
    return rand(100000,999999);
  }
  
}