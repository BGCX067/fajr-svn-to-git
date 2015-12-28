<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains CodeSnippet which can extract source code near specific line.
 *
 * @package    Fajr
 * @subpackage Libfajr__Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>; original source: fks.sk database
 * @filesource
 */
namespace fajr\libfajr\util;

/**
 * Provides simple way of extracting source code near specific line.
 *
 * @package    Fajr
 * @subpackage Libfajr__Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>; original source: fks.sk database
 * @filesource
 */
class CodeSnippet
{
  /**
   * Return string containing lines near to $line in file $file.
   *
   * @param string $file file from get content
   * @param string $line line which we are interested in
   * @param int    $distance
   *
   * @returns string lines in range <$line - $distance, $line + $distance>
   * (starting with line number, ending with newline)
   *
   * @TODO Possible recursive asserts?
   */
  public static function getCodeSnippet($file, $line, $distance)
  {
    assert(is_string($file)); //ehm, recursive asserts?
    assert(is_int($line));

    $code = "";

    $f = fopen($file, "r");
    if ($f) {
      $lineNum = 0;
      while (!feof($f)) {
        $buf = fgets($f, 4096);
        $lineNum++;

        if (abs($lineNum - $line) <= $distance) {
          $code .=sprintf("%3d", $lineNum).":  ".$buf;
        }
      }
      fclose($f);
    }

    return $code;
  }

}
