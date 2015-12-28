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
class HodnoteniaRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'semester',
          1 => 'kodCastSP',
          2 => 'kodTypVyucbySP',
          3 => 'skratka',
          4 => 'nazov',
          5 => 'kredit',
          6 => 'kodSposUkon',
          7 => 'termin',
          8 => 'znamka',
          9 => 'datum',
          10 => 'uznane',
          11 => 'blokPopis',
          12 => 'poplatok',
          13 => 'nahradzaMa',
          14 => 'nahradzam',
          15 => 'znamkaPopis',
          16 => 'dovezene',
          17 => 'mozePrihlasit',
          18 => 'popisSposUkon',
          19 => 'rozsah',
          20 => 'priebHodn',
          );
  }
}
