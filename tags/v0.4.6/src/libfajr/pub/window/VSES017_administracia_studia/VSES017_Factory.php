<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains factory for all screens from management of study.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\window\VSES017_administracia_studia as VSES017;
use fajr\libfajr\pub\base\Trace;

/**
 * Provides instances of screens in study management part of ais.
 */
interface VSES017_Factory
{

  /**
   * @returns AdministraciaStudiaScreen
   */
  public function newAdministraciaStudiaScreen(Trace $trace);

  /**
   * @param $idZapisnyList id (nie index) zapisneho listu
   * @param $idStudium id (nie index) studia
   * @returns TerminyHodnoteniaScreen
   */
  public function newTerminyHodnoteniaScreen(Trace $trace, $idZapisnyList, $idStudium);

  /**
   * @param $idZapisnyList id (nie index) zapisneho listu
   *
   * @returns HodnoteniaPriemeryScreen
   */
  public function newHodnoteniaPriemeryScreen(Trace $trace, $idZapisnyList);
}
