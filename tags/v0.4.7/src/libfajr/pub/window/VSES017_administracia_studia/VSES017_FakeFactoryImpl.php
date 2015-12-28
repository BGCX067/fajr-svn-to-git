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

use fajr\libfajr\window\VSES017_administracia_studia as VSES017;
use fajr\libfajr\window\VSES017_administracia_studia\fake as VSES017fake;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\window\fake\FakeRequestExecutor;
use sfStorage;

class VSES017_FakeFactoryImpl implements VSES017_Factory
{
  /** @var sfStorage session storage to save modifications to defaults */
  private $storage;

  public function __construct(sfStorage $sessionStorage)
  {
    $this->storage = $sessionStorage;
  }

  public function newAdministraciaStudiaScreen(Trace $trace)
  {
    return new VSES017fake\FakeAdministraciaStudiaScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()));
  }

  public function newTerminyHodnoteniaScreen(Trace $trace, $idZapisnyList, $idStudium)
  {
    return new VSES017fake\FakeTerminyHodnoteniaScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()), $idZapisnyList);
  }

  public function newHodnoteniaPriemeryScreen(Trace $trace, $idZapisnyList)
  {
    return new VSES017fake\FakeHodnoteniaPriemeryScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()),
        $idZapisnyList);
  }
}
