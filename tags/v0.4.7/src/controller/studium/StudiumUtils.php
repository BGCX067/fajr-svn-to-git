<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

fields::autoload();

/**
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class StudiumUtils
{
  /**
   * ked odhlasujeme z predmetu, narozdiel od AISu robime opat
   * inicializaciu vsetkych aplikacii. Just for sure chceme
   * okontrolovat, ze sa nic nezmenilo a ze sme dostali rovnake data
   * ako predtym!
   */
  public static function hashNaOdhlasenie($mojeTerminyRow)
  {
    $data = array($mojeTerminyRow[MojeTerminyFields::INDEX],
                  $mojeTerminyRow[MojeTerminyFields::DATUM],
                  $mojeTerminyRow[MojeTerminyFields::CAS],
                  $mojeTerminyRow[MojeTerminyFields::PREDMET_SKRATKA]);
    return md5(implode('|', $data));
  }


  public static function hashNaPrihlasenie($predmetSkratka, $zoznamTerminovRow)
  {
    $data = array($zoznamTerminovRow[ZoznamTerminovFields::INDEX],
                  $zoznamTerminovRow[ZoznamTerminovFields::DATUM],
                  $zoznamTerminovRow[ZoznamTerminovFields::CAS],
                  $predmetSkratka);
    return md5(implode('|', $data));
  }

}
