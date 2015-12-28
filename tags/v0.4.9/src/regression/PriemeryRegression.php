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
class PriemeryRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'priemerInfoPopisAkadRok',
          1 => 'priemerInfoKodSemester',
          2 => 'vazPriemer',
          3 => 'studPriemer',
          4 => 'pocetPredmetov',
          5 => 'pocetNeabs',
          6 => 'pokusyPriemer',
          7 => 'ziskanyKredit',
          8 => 'prerusUkon',
          9 => 'priemerInfoDatum',
          10 => 'priemerInfoDatum1Hodn',
          11 => 'priemerInfoDatum2Hodn',
          12 => 'priemerNazov',
          13 => 'priemerZaAkRok',
          14 => 'priemerZaSemester',
          15 => 'priemerLenStudPlan',
          16 => 'priemerUznanePredm',
          17 => 'priemerAjDatum1Hodn',
          18 => 'priemerAjDatum2Hodn',
          19 => 'priemerPocitatNeabs',
          20 => 'priemerVahaNeabsolvovanych',
          21 => 'priemerSkratkaOrganizacnaJednotka',
          22 => 'priemerPocitatNeabsC',
          23 => 'pocetPredmetovVyp',
          24 => 'priemerInfoStudentiVypoctu',
          );
  }
}
