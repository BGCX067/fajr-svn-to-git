<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia;

use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\base\Trace;
use fajr\libfajr\connection\SimpleConnection;
use fajr\libfajr\data_manipulation\AIS2TableParser;
/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class HodnoteniaPriemeryScreen extends AIS2AbstractScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, SimpleConnection $connection, $idZapisnyList,
      AIS2TableParser $parser = null)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES212App';
    $data->additionalParams = array('kodAplikacie' => 'VSES212',
        'idZapisnyList' => $idZapisnyList);
    $requestBuilder = new RequestBuilderImpl();
    $executor = new ScreenRequestExecutor($requestBuilder);
    parent::__construct($trace, $executor, $data);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
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
