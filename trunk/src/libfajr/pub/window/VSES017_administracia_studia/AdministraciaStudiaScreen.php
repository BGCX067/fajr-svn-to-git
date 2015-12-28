<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\base\Trace;

interface AdministraciaStudiaScreen extends LazyDialog
{
  const ACTION_HODNOTENIA_PRIEMERY = 'hodnoteniaPriemeryAction';
  const ACTION_TERMINY_HODNOTENIA = 'terminyHodnoteniaAction';
  
  /**
   * @returns SimpleDataTable
   */
  public function getZoznamStudii(Trace $trace);

  /**
   * @returns SimpleDataTable
   */
  public function getZapisneListy(Trace $trace, $studiumIndex);

  public function getZapisnyListIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action);

  public function getStudiumIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action);
  
  public function getPrehladKreditovDialog(Trace $trace, $studiumIndex);
}
