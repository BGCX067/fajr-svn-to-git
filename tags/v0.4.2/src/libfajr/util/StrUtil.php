<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\util;
use fajr\libfajr\base\Preconditions;

class StrUtil {
  /**
   * Searches haystack for perl-like pattern and
   * returns first sub-match from pattern.
   * I.e. If the pattern is "example:(.*)",
   * the full match is example:something and this
   * function returns "something"
   *
   * @param string $pattern perl-like pattern with 1 sub-match
   * @param string $haystack
   *
   * @returns string sub-match or false on failure
   */
  public static function match($pattern, $haystack)
  {
    Preconditions::checkIsString($pattern);
    Preconditions::checkIsString($haystack);
    $matches = array();
    if (!preg_match($pattern, $haystack, $matches)) {
      return false;
    }
    assert(isset($matches[1]));
    return $matches[1];
  }

  /**
   * Searches the haystack for perl-like pattern and
   * return an array of all submatches
   *
   * @param string $pattern perl-like pattern
   * @param string $haystack
   *
   * @returns array of submatches or false on failure
   */
  public static function matchAll($pattern, $haystack)
  {
    Preconditions::checkIsString($pattern);
    Preconditions::checkIsString($haystack);
    $matches = array();
    if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) {
      return false;
    }
    return $matches[0];
  }

  /**
   * Checks whether $haystack starts with a substring $needle
   *
   * @param string $haystack
   * @param string $needle
   *
   * @returns bool true if $haystack starts with $needle, false otherwise
   */
  public static function startsWith($haystack, $needle)
  {
    Preconditions::checkIsString($haystack);
    Preconditions::checkIsString($needle);
    // TODO(ppershing): why can't we use simply
    // substr($haystack, 0, strlen($needle)) === $needle ?
    // anty: substr makes copy, it may have memory and speed problems.
    if ($needle == '') return true;

    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, 0, $needle_length) === 0;
  }

  /**
   * Checks whether $haystack ends with a substring $needle
   *
   * @param string $haystack
   * @param string $needle
   *
   * @returns bool true if $haystack ends with $needle, false otherwise
   */
  public static function endsWith($haystack, $needle)
  {
    Preconditions::checkIsString($haystack);
    Preconditions::checkIsString($needle);
    if ($needle == '') return true;

    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, -$needle_length, $needle_length) === 0;
  }
}
?>
