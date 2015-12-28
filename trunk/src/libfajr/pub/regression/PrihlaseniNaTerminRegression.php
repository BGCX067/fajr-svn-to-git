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
class PrihlaseniNaTerminRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'meno',
          1 => 'priezvisko',
          2 => 'skratka',
          3 => 'datumPrihlas',
          4 => 'plneMeno',
          5 => 'rocnik',
          6 => 'kruzok',
          );
  }
}
