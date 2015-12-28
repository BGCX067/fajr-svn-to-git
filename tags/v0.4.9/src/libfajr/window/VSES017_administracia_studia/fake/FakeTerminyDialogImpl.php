<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje diálóg s termínami skúšok k predmetu.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\window\VSES017_administracia_studia\fake;


use Exception;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\data_manipulation\Znamka;
use fajr\libfajr\pub\window\VSES017_administracia_studia\TerminyDialog;
use fajr\libfajr\window\fake\FakeAbstractDialog;
use fajr\libfajr\window\fake\FakeRequestExecutor;
use fajr\regression\TerminyKPredmetuRegression;

/**
 * Trieda pre dialóg s termínmi skúšok k jednému predmetu.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeTerminyDialogImpl extends FakeAbstractDialog
    implements TerminyDialog
{
  
  public function getZoznamTerminov(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(array(), 'terminy');
    $result = array();
    foreach ($data as $index => $value) {
      $info = $this->executor->readTable(array('termin' => $index), 'prihlas');
      if (isset($info['jePrihlaseny']) && $info['jePrihlaseny']) {
        // skip this in list
      } else {
        $result[$index] = $value;
      }
    }
    return new DataTableImpl(TerminyKPredmetuRegression::get(), $result);
  }

  const MOJE_TERMINY_ZNAMKA = 12;

  public function prihlasNaTermin(Trace $trace, $terminIndex)
  {
    $terminy = $this->executor->readTable(array(), 'terminy');
    // Jemna napodobenina logiky pre prihlasovanie,
    // checkuje ci uz nie som zapisany na iny termin s vyslednou znamkou.
    foreach ($terminy as $index => $unused) {
      $info = $this->executor->readTable(array('termin' => $index), 'prihlas');
      if (isset($info['jePrihlaseny']) && $info['jePrihlaseny']) {
        $znamka = $info['prihlasenyData'][self::MOJE_TERMINY_ZNAMKA];
        if (!Znamka::isSame($znamka, 'Fx')) {
          throw new Exception("Už si prihlásený na iný termín z tohoto predmetu!");
        }
      }
    }
    $info = $this->executor->readTable(array('termin' => $terminIndex), 'prihlas');
    if (!isset($info['mozePrihlasit']) || !$info['mozePrihlasit']) {
      $dovod = isset($info['nemozePrihlasitDovod']) ? $info['nemozePrihlasitDovod'] : 'neznámy';
      throw new Exception("Nemôžem prihlásiť na termín - dôvod: " . $dovod);
    }
    if (isset($info['jePrihlaseny']) && $info['jePrihlaseny']) {
      throw new Exception("Na daný termín si už prihlásený.");
    }
    $info['jePrihlaseny'] = true;
    $this->executor->writeTable(array('termin' => $terminIndex), 'prihlas', $info);


    $this->closeIfNeeded($trace);
    return true;
  }

  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $data = array('termin' => $terminIndex);
    return new FakeZoznamPrihlasenychDialogImpl($trace, $this, $data);
  }
  
}
?>
