<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSST060_register_predmetov;

use fajr\libfajr\pub\window\VSST060_register_predmetov\RegisterPredmetovScreen;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use fajr\libfajr\util\StrUtil;
use fajr\libfajr\util\MiscUtil;
use fajr\libfajr\pub\exceptions\ParseException;
// TODO checknut ci vsetky hentie use stale treba (su skopcene z VSES017)

/**
 * Trieda reprezentujúca jednu obrazovku s registrom predmetov.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class RegisterPredmetovScreenImpl extends AIS2AbstractScreen
    implements RegisterPredmetovScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor, AIS2TableParser $parser)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.st.VSST060App';
    $data->additionalParams = array('kodAplikacie' => 'VSST060');
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  public function getInformacnyList(Trace $trace, $kodPredmetu)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'zobrazitPredmetyAction',
              'embObj' => array(
                'objName' => 'skratkaPredmetuTextField',
                'text' => $kodPredmetu
              ),
            ));
    $table = $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
        $data, 'VSST060_RegisterPredmetovDlg0_zoznamPredmetovTable_dataView');
    if(count($table->getData()) == 0) {
      throw new ParseException("Daný kód predmetu sa v zozname nenachádza.");
      return;
    }

    // TODO: nikde nevyberame, ktoreho vrateneho riadku informacny list chceme...
    // asi sa defaultne pouzije prvy - je to OK?
    
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'informacnyListAction')
    );
    if (!preg_match("@dm\(\)\.openDialog\("
          ."\"CM024_InformListVyberMoznostiDlg1\"@", $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'enterAction',
              'dlgName' => 'CM024_InformListVyberMoznostiDlg1',
              'embObj' => array(
                'objName' => 'typZostavyComboBox',
                'dataView' => array(
                  'selectedIndexes' => 1,
                ),
              ),
            ));
    if (!preg_match("@dm\(\)\.closeDialog\("
          ."\"CM024_InformListVyberMoznostiDlg1\"\);@", $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('events' => false,
              'app' => false,
              'dlgName' => false,
              'changedProperties' => array('confirmResult' => '-1')
            ));
    if(!preg_match('@shellExec@', $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    return $this->executor->doFilesRequest($trace, array('file' => '', 'contentType' => ''));
  }

}
?>
