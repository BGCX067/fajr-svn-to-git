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
class ZoznamZapisnychListovRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'popisAkadRok',
          1 => 'rokRocnik',
          2 => 'studProgramSkratka',
          3 => 'studProgramPopis',
          4 => 'studProgramDoplnUdaje',
          5 => 'datumZapisu',
          6 => 'poplatok',
          7 => 'podmienecne',
          8 => 'studProgramDlzka',
          9 => 'studProgramIdEviCRS',
          10 => 'studProgramIdProgramCRS',
          11 => 'datumSplnenia',
          12 => 'priznak',
          13 => 'studProgramSkratkaAkreditOJ',
          14 => 'typFinacovaniaPopis',
          15 => 'typFinacovaniaSkratPopis',
          16 => 'uzatvorenyLS',
          17 => 'datumUzamknutia',
          18 => 'modifikator',
          19 => 'poslModif',
          20 => 'uzatvorenyZS',
          21 => 'nespoplatneRoky',
          );
  }
}
