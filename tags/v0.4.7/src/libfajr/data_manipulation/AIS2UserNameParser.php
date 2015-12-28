<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Username parser for AIS html pages.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\util\StrUtil;
use fajr\libfajr\base\Preconditions;

/**
 * Parses user name from AIS html response.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2UserNameParser {
  /**
   * Regexp pattern for username.
   */
  const USERNAME_PATTERN =
      '@\<div class="user-name"\>(?P<username>[^<]*)\</div\>@';

  /**
   * Parses user name from AIS2 start page
   *
   * @param string $html AIS2 html reply to be parsed
   *
   * @returns AIS2Version AIS2 version
   * @throws ParseException on error
   */
  public function parseUserNameFromMainPage($html) {
    Preconditions::checkIsString($html);
    $data = StrUtil::matchAll(self::USERNAME_PATTERN, $html);
    if ($data === false) {
      throw new ParseException("Cannot parse username from response.");
    }
    return $data['username'];
  }
}
