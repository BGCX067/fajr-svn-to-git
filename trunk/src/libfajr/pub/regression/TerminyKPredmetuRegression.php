<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains AIS2 table description for regression checking.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\pub\regression;

/**
 * Contains AIS2 table description.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TerminyKPredmetuRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get() {
    return
      array (
        0 => 'kodFaza',
        1 => 'dat',
        2 => 'cas',
        3 => 'miestnosti',
        4 => 'pocetPrihlasenych',
        5 => 'maxPocet',
        6 => 'pocetHodn',
        7 => 'hodnotiaci',
        8 => 'prihlasovanie',
        9 => 'odhlasovanie',
        10 => 'poznamka',
        11 => 'zaevidoval',
        12 => 'moznostPrihlasenia',
      );
  }
}
