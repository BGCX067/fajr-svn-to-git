<?php
/**
 * Contains utilities for Trace implementations
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\util;

use fajr\libfajr\base\Preconditions;

/**
 * Utilities for trace implementations
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TraceUtil
{

  /**
   * Finds appropriate caller data associated with stacktrace.
   *
   * @param int $depth How much back in stack we should go.
   *                   Zero defaults to caller of this function.
   *
   * @returns array @see debug_backtrace for details
   */
  public static function getCallerData($depth) {
    $data = debug_backtrace();
    for ($i = 0; $i < $depth + 1; $i++) {
      array_shift($data);
    }
    $caller = array_shift($data);
    return $caller;
  }

}
