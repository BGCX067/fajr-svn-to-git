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

use fajr\libfajr\pub\window\VSES017_administracia_studia\TerminyHodnoteniaScreen;
use fajr\libfajr\pub\base\Trace;

use Exception;
use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\window\fake\FakeAbstractScreen;
use fajr\libfajr\window\fake\FakeRequestExecutor;
use fajr\regression\ZapisanePredmetyRegression;
use fajr\libfajr\base\Preconditions;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeTerminyHodnoteniaScreenImpl extends FakeAbstractScreen
    implements TerminyHodnoteniaScreen
{
  private $idZapisnyList;

  public function __construct(Trace $trace, FakeRequestExecutor $executor, $idZapisnyList)
  {
    parent::__construct($trace, $executor);
    $this->idZapisnyList = $idZapisnyList;
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
        array('list' => $this->idZapisnyList),
        'zapisanePredmety');
    $table = new DataTableImpl(ZapisanePredmetyRegression::get(), $data);
    return $table;
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    return new DataTableImpl(array(), array());
    $data = $this->executor->requestContent($trace);

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'terminyTable_dataView');
  }

  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex)
  {
    $data = array('list' => $this->idZapisnyList,
                  'predmet' => $predmetIndex);
    return new FakeTerminyDialogImpl($trace, $this, $data);
  }

  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $data = new DialogData();
    $data->compName = 'zoznamPrihlasenychStudentovAction';
    $data->embObjName = 'terminyTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialogImpl($trace, $this, $data);
  }
  
  public function odhlasZTerminu(Trace $trace, $terminIndex)
  {
    $this->openIfNotAlready($trace);
    // Posleme request ze sa chceme odhlasit.
    $data = $this->executor->doRequest($trace, array(
      'compName' => 'odstranitTerminAction',
      'eventClass' => 'avc.ui.event.AVCActionEvent',
      'embObj' => array(
        'objName' => 'terminyTable',
        'dataView' => array(
          'activeIndex' => $terminIndex,
          'selectedIndexes' => $terminIndex,
        ),
      ),
    ));
    if (!preg_match("@Skutočne chcete odobrať vybraný riadok?@", $data)) {
      throw new Exception("Problém pri odhlasovaní - neočakávaná odozva AISu");
    }
    
    // Odklikneme konfirmacne okno ze naozaj.
    $data = $this->executor->doRequest($trace, array(
      'events' => false,
      'app' => false,
      'dlgName' => false,
      'changedProperties' => array(
        'confirmResult' => 2,
      ),
    ));
    
    $message = match($data, '@webui\.messageBox\("([^"]*)"@');
    if (($message !== false) && ($message != 'Činnosť úspešne dokončená.')) {
      throw new Exception("Z termínu sa (pravdepodobne) nepodarilo odhlásiť." .
                          "Dôvod:<br/><b>".$message.'</b>');
    }

    if (!preg_match("@dm\(\).setActiveDialogName\(".
          "'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0'\);@", $data)) {
      throw new Exception("Problém pri odhlasovaní - neočakávaná odpoveď od AISu");
    }
    
    return true;
  }

}

?>
