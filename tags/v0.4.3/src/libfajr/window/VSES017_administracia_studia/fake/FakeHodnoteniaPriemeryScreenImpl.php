<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia\fake;

use fajr\libfajr\pub\window\VSES017_administracia_studia\HodnoteniaPriemeryScreen;
use fajr\libfajr\pub\base\Trace;

use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\window\fake\FakeAbstractScreen;
use fajr\libfajr\window\fake\FakeRequestExecutor;
use fajr\regression\HodnoteniaRegression;
use fajr\regression\PriemeryRegression;
use fajr\libfajr\base\Preconditions;
/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeHodnoteniaPriemeryScreenImpl extends FakeAbstractScreen
    implements HodnoteniaPriemeryScreen
{
  private $idZapisnyList;

  public function __construct(Trace $trace, FakeRequestExecutor $executor, $idZapisnyList)
  {
    parent::__construct($trace, $executor);
    $this->idZapisnyList = $idZapisnyList;
  }

  public function getHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
        array('list' => $this->idZapisnyList),
        'hodnotenia');
    $table = new DataTableImpl(HodnoteniaRegression::get(), $data);
    return $table;
  }

  public function getPriemery(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
        array('list' => $this->idZapisnyList),
        'priemery');
    $table = new DataTableImpl(PriemeryRegression::get(), $data);
    return $table;
  }

}

?>
