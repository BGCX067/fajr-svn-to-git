<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Version parser for AIS html pages.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\util\StrUtil;

/**
 * Parses AIS2 html response and finds AIS2 version.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2VersionParser {
  /**
   * Regexp pattern for AIS2 version.
   */
  const VERSION_PATTERN =
      '@\<div class="verzia"\>AiS2 verzia 2\.(?P<major>[0-9]+)\.(?P<minor>[0-9]+)\.(?<patch>[0-9]+)\</div\>@';

  /**
   * Parses the AIS2 version from html page.
   *
   * @param string $html AIS2 html reply to be parsed
   *
   * @returns AIS2Version AIS2 version
   * @throws ParseException on error
   */
  public function parseVersionStringFromMainPage($html) {
    $data = StrUtil::matchAll(self::VERSION_PATTERN, $html);
    if ($data === false) {
      throw new ParseException("Cannot parse AIS version from response.");
    }
    return new AIS2Version(2, $data['major'], $data['minor'], $data['patch']);
  }
}
