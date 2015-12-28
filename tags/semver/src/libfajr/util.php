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

  /**
   * Function that searches haystack for perl-like pattern and
   * returns first sub-match from pattern.
   * I.e. If the pattern is "example:(.*)",
   * the full match is example:something and this
   * function returns "something"
   */
  function match($pattern, $haystack)
  {
    $matches = array();
    if (!preg_match($pattern, $haystack, $matches)) {
      return false;
    }
    assert(isset($matches[1]));
    return $matches[1];
  }
  
  function matchAll($pattern, $haystack, $singleMatch = false)
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
?>
