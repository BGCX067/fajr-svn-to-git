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
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia\fake;

use fajr\libfajr\pub\window\VSES017_administracia_studia\TerminyDialog;
use fajr\libfajr\pub\base\Trace;

use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\window\fake\FakeAbstractDialog;
use fajr\libfajr\window\fake\FakeRequestExecutor;
use fajr\regression\TerminyKPredmetuRegression;
use fajr\libfajr\base\Preconditions;
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
    return new DataTableImpl(TerminyKPredmetuRegression::get(), $data);
  }
  
  public function prihlasNaTermin(Trace $trace, $terminIndex)
  {
    assert(false);
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace, array(
      'compName' => 'enterAction',
      'eventClass' => 'avc.ui.event.AVCActionEvent',
      'embObj' => array(
        'objName' => 'zoznamTerminovTable',
        'dataView' => array(
          'activeIndex' => $terminIndex,
          'selectedIndexes' => $terminIndex,
        ),
      ),
    ));
    
    $error = match($data, '@webui\(\)\.messageBox\("([^"]*)",@');
    if ($error) {
      throw new Exception('Nepodarilo sa prihlásiť na zvolený termín.<br/>'.
          'Dôvod: <b>'.$error.'</b>');
    }
    if (!preg_match("@dm\(\)\.closeDialog\("
          ."\"VSES206_VyberTerminuHodnoteniaDlg1\"\);@", $data)) {
      throw new Exception("Problém pri prihlasovaní: ".
          "Neočakávaná odozva od AISu");
    }
    
    $this->closeIfNeeded($trace);
    return true;
  }
  
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    assert(false);
    $data = new DialogData();
    $data->compName = 'zobrazitZoznamPrihlasenychAction';
    $data->embObjName = 'zoznamTerminovTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialogImpl($trace, $this, $data);
  }
  
}
?>
