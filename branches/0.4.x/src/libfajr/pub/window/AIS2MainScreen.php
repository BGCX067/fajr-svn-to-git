<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Represents "start" screen of AIS.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\pub\window;

use fajr\libfajr\pub\base\Trace;

/**
 * Represents main page of AIS.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface AIS2MainScreen
{
  /**
   * @returns AIS2Version
   */
  public function getAisVersion(Trace $trace);

  /**
   * Get the names of all available ais applications
   * from ais menu.
   *
   * @returns array(string) names of applications
   */
  public function getAllAvailableApplications(Trace $trace);

  /**
   * Get full name of the user.
   * Note: resulting string is not sanitized and shouldn't be
   * used by file/other access.
   *
   * @returns string
   */
  public function getFullUserName(Trace $trace);
}
