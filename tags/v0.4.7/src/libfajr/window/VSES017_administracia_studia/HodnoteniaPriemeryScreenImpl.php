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
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\VSES017_administracia_studia\HodnoteniaPriemeryScreen;
use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\data_manipulation\AIS2TableParser;

/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 */
class HodnoteniaPriemeryScreenImpl extends AIS2AbstractScreen
    implements HodnoteniaPriemeryScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor,
      AIS2TableParser $parser, $idZapisnyList)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES212App';
    $data->additionalParams = array('kodAplikacie' => 'VSES212',
        'idZapisnyList' => $idZapisnyList);
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  // TODO(ppershing): Maybe cache data between getHodnotenia && getPriemery

  public function getHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'hodnoteniaTable_dataView');
  }

  public function getPriemery(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'priemeryTable_dataView');
  }

}

?>
