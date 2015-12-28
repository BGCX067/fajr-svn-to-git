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

  /**
   * Function that searches haystack for perl-like pattern and
   * returns first sub-match from pattern.
   * I.e. If the pattern is "example:(.*)",
   * the full match is example:something and this
   * function returns "something"
   *
   * @deprecated, use fajr\libfajr\util version
   */
  function match($haystack, $pattern)
  {
    $matches = array();
    if (!preg_match($pattern, $haystack, $matches)) {
      return false;
    }
    assert(isset($matches[1]));
    return $matches[1];
  }
  
  /**
   * Function that searches haystack for perl-like pattern and
   * returns an array of all submatches from pattern.
   *
   * @deprecated, use fajr\libfajr\util version
   */
  function matchAll($haystack, $pattern, $singleMatch = false)
  {
    $matches = array();
    if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) {
      return false;
    }
    else
    {
      if ($singleMatch == false) {
        return $matches;
      }
      else {
        return $matches[0];
      }
    }
  }

  /**
   * Function which removes all integer-indexed entries from array.
   * Useful for removing unnamed matches after matchAll with named patterns.
   * @param array() $data Array containing string&integer indexed values 
   * @returns array() Data wihout any integer-indexed values
   */
  function removeIntegerIndexesFromArray($data)
  {
    foreach (array_keys($data) as $key) {
      if (is_numeric($key)) {
        unset($data[$key]);
      }
    }
    return $data;
  }
  
  function utf8_sanitize($string)
  {
    $out = @iconv("UTF-8", "UTF-8//IGNORE", $string);
    if ($string != $out) {
      $out = "Warning: String not shown for security reasons: " .
             "String contains invalid utf-8 charactes.";
    }
    return $out;

  }

  function hescape($string)
  {
    return htmlspecialchars(utf8_sanitize($string), ENT_QUOTES, 'UTF-8');
  }

  function random()
  {
    return rand(100000,999999);
  }
  
?>
