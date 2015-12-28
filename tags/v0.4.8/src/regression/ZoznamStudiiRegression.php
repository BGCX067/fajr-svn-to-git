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
class ZoznamStudiiRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'rokDoporuceny',
          1 => 'studijnyProgramSkratka',
          2 => 'kodKruzok',
          3 => 'studijnyProgramPopis',
          4 => 'studijnyProgramDoplnUdaje',
          5 => 'zaciatokStudia',
          6 => 'koniecStudia',
          7 => 'studijnyProgramDlzka',
          8 => 'dobaStudia',
          9 => 'cisloDiplomu',
          10 => 'cisloMatriky',
          11 => 'cisloVysvedcenia',
          12 => 'cisloDodatku',
          13 => 'studijnyProgramIdEviCRS',
          14 => 'studijnyProgramIdProgramCRS',
          15 => 'priznak',
          16 => 'studijnyProgramSkratkaAkreditOJ',
          17 => 'rokStudia',
          18 => 'idEvi',
          19 => 'modifikator',
          20 => 'poslModif',
          );
  }
}
