<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains AIS2 table description for regression checking.
 *
 * @package    Fajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\regression;

/**
 * Contains AIS2 table description.
 *
 * @package    Fajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ZapisanePredmetyRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'kodCastStPlanu',
          1 => 'kodTypVyucby',
          2 => 'skratka',
          3 => 'nazov',
          4 => 'kredit',
          5 => 'kodSemester',
          6 => 'kodSposUkon',
          7 => 'pocetTerminov',
          8 => 'pocetAktualnychTerminov',
          9 => 'aktualnost',
          );
  }
}
