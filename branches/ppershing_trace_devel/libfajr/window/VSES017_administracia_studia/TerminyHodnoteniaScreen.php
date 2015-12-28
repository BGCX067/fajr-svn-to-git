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

use fajr\libfajr\base\Trace;
use fajr\libfajr\connection\SimpleConnection;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\ScreenData;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\window\AIS2AbstractScreen;
use fajr\libfajr\data_manipulation\AIS2TableParser;
/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class TerminyHodnoteniaScreen extends AIS2AbstractScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, SimpleConnection $connection, $idZapisnyList,
      $idStudium, AIS2TableParser $parser = null)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES007App';
    $data->additionalParams = array('kodAplikacie' => 'VSES007',
        'idZapisnyList' => $idZapisnyList,
        'idStudium' => $idStudium);
    $requestBuilder = new RequestBuilderImpl();
    $executor = new ScreenRequestExecutor($requestBuilder);
    parent::__construct($trace, $executor, $data);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $data,
        'predmetyTable_dataView');
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'terminyTable_dataView');
  }

  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex)
  {
    $data = new DialogData();
    $data->compName = 'pridatTerminAction';
    $data->embObjName = 'predmetyTable';
    $data->index = $predmetIndex;

    return new TerminyDialog($trace, $this, $data);
  }
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $data = new DialogData();
    $data->compName = 'zoznamPrihlasenychStudentovAction';
    $data->embObjName = 'terminyTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialog($trace, $this, $data);
  }
  
  public function odhlasZTerminu(Trace $trace, $terminIndex)
  {
    $this->openIfNotAlready($trace);
    // Posleme request ze sa chceme odhlasit.
    $data = $this->requestData(array(
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
    
    // Odklikneme konfirmacne okno ze naozaj.
    $data = $this->requestData(array(
      'events' => false,
      'app' => false,
      'dlgName' => false,
      'changedProperties' => array(
        'confirmResult' => 2,
      ),
    ));
    
    if (!preg_match('@dialogManager\.openDialog\("PleaseWaitDlg0"@', $data)) throw new Exception('Z termínu sa nepodarilo odhlásiť.<br/>Pravdepodobne termín s daným indexom neexistuje.');
    
    // Nacitame loading obrazovku.
    $data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&form=PleaseWaitDlg0&antiCache='.random());
    
    // Zavrieme loading obrazovku. Az po tomto kroku sme naozaj odhlaseni.
    $data = $this->requestData(array(
      'events' => false,
      'dlgName' => false,
      'appProperties' => array(
        'activeDlgName' => 'PleaseWaitDlg0',
      ),
    ));
    
    $message = match($data, '@webui\.messageBox\("([^"]*)"@');
    if (($message !== false) && ($message != 'Činnosť úspešne dokončená.')) throw new Exception("Z termínu sa (pravdepodobne) nepodarilo odhlásiť. Dôvod:<br/><b>".$message.'</b>');
    
    return true;
  }

}

?>
