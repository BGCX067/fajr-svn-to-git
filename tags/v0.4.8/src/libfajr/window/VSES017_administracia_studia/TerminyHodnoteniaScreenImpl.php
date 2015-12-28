<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\VSES017_administracia_studia\TerminyHodnoteniaScreen;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use Exception;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 */
class TerminyHodnoteniaScreenImpl extends AIS2AbstractScreen
    implements TerminyHodnoteniaScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor,
      AIS2TableParser $parser, $idZapisnyList, $idStudium)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES007App';
    $data->additionalParams = array('kodAplikacie' => 'VSES007',
        'idZapisnyList' => $idZapisnyList,
        'idStudium' => $idStudium);
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace,
      array('eventClass' => 'avc.ui.event.AVCActionEvent',
        'compName' => 'filterAction',
        'embObj' => array(
          'objName' => 'semesterComboBox',
          'dataView' => array(
            'selectedIndexes' => 0,
          ),
        ),
    ));

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $data,
        'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0_predmetyTable_dataView');
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace,
      array('eventClass' => 'avc.ui.event.AVCActionEvent',
        'compName' => 'zobrazitTerminyAction',
        'embObj' => array(
          'objName' => 'zobrazitTerminyComboBox',
          'dataView' => array(
            'selectedIndexes' => 0,
          ),
        ),
    ));

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0_terminyTable_dataView');
  }

  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex)
  {
    $data = new DialogData();
    $data->compName = 'pridatTerminAction';
    $data->embObjName = 'predmetyTable';
    $data->index = $predmetIndex;

    return new TerminyDialogImpl($trace, $this, $data);
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
      throw new Exception("Problém pri odhlasovaní z termínu - neočakávaná odpoveď od AISu");
    }
    
    return true;
  }

}
