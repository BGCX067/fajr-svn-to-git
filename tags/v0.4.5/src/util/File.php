<?php
/**
 * Contains abstraction representing open file
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */

namespace fajr\util;

/**
 * Represents a open file
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
interface File {

  /**
   * Read up to $bytes bytes from this file.
   *
   * If there is an EOF, return a empty string.
   *
   * @see fread
   * @see http://www.php.net/manual/en/function.fread.php#95413
   * @returns string Up to $bytes bytes in a string
   */
  public function read($bytes);

  /**
   * Write $string to this file
   * @see fwrite
   */
  public function write($string);

  /**
   * Force underlying file to be written to storage
   * @see fflush
   */
  public function flush();

  /**
   * Close file and free up any system resources
   * @see fclose
   */
  public function close();

}
