<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia\fake;

use fajr\libfajr\pub\window\VSES017_administracia_studia\PrehladKreditovDialog;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\window\fake\FakeAbstractDialog;
use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\pub\regression\PrehladKreditovRegression;

/**
 * Trieda pre dialóg s prehľadom kreditov
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class FakePrehladKreditovDialogImpl extends FakeAbstractDialog
    implements PrehladKreditovDialog
{
  
  public function getPredmety(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
          array(),
          'prehladKreditov');
    return new DataTableImpl(PrehladKreditovRegression::get(), $data);
  }
}
