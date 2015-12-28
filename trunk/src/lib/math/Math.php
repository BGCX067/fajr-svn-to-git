<?php
/**
 * Contains useful math functions
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Math
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\lib\math;
use fajr\libfajr\base\Preconditions;

/**
 * Utility class containing useful math functions.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Math
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Math {
  /**
   * Compute square of the argument
   *
   * @param numeric $value
   *
   * @returns numeric $value * $value
   */
  public static function sqr($value)
  {
    Preconditions::check(is_numeric($value));
    return $value * $value;
  }
}
