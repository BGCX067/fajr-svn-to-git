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
class MojeTerminyRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'jePrihlaseny',
          1 => 'kodFaza',
          2 => 'dat',
          3 => 'cas',
          4 => 'miestnosti',
          5 => 'pocetPrihlasenych',
          6 => 'datumPrihlas',
          7 => 'datumOdhlas',
          8 => 'zapisal',
          9 => 'pocetHodn',
          10 => 'hodnotiaci',
          11 => 'maxPocet',
          12 => 'znamka',
          13 => 'prihlasovanie',
          14 => 'odhlasovanie',
          15 => 'poznamka',
          16 => 'zaevidoval',
          17 => 'mozeOdhlasit',
          18 => 'predmetSkratka',
          19 => 'predmetNazov',
          20 => 'hodnPredmetu',
          21 => 'moznostPrihlasenia',
          );
  }
}
