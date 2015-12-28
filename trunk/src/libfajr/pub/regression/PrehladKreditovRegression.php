<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains AIS2 table description for regression checking.
 *
 * @package    Fajr
 * @subpackage Regression
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\pub\regression;

/**
 * Contains AIS2 table description.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Regression
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class PrehladKreditovRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
        0 => 'akRok',
        1 => 'skratka',
        2 => 'nazov',
        3 => 'kodTypVyucbySP',
        4 => 'semester',
        5 => 'kredit',
        6 => 'kodSposUkon',
        7 => 'znamka',
        8 => 'termin',
        9 => 'datum',
        10 => 'uznane',
        11 => 'blokPopis',
        12 => 'poplatok',
        13 => 'nahradzaMa',
        14 => 'nahradzam',
        15 => 'rozsah',
        16 => 'canAdd',
        17 => 'znamkaPopis',
        18 => 'dovezene',
        19 => 'mozePrihlasit',
        20 => 'popisSposUkon',
        21 => 'priebHodn',
      );
  }
}
