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
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\DialogParent;
use fajr\libfajr\window\AIS2AbstractDialog;
use fajr\libfajr\data_manipulation\AIS2TableParser;
/**
 * Trieda pre dialóg s termínmi skúšok k jednému predmetu.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class TerminyDialog extends AIS2AbstractDialog
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, DialogParent $parent,
      DialogData $data, AIS2TableParser $parser = null)
  {
    parent::__construct($trace, $parent, $data);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }
  
  public function getZoznamTerminov(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $response = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $response,
        'zoznamTerminovTable_dataView');
  }
  
  public function prihlasNaTermin(Trace $trace, $terminIndex)
  {
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
    
    $error = match($data, '@webui\.messageBox\("([^"]*)",@');
    if ($error) throw new Exception('Nepodarilo sa prihlásiť na zvolený termín.<br/>Dôvod: <b>'.$error.'</b>');
    
    $this->terminated = true; // po uspesnom prihlaseni za dialog hned zavrie
    return true;
  }
  
  public function getZoznamPrihlasenychDialog($terminIndex)
  {
    $data = new DialogData();
    $data->compName = 'zobrazitZoznamPrihlasenychAction';
    $data->embObjName = 'zoznamTerminovTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialog($trace, $this, $data);
  }
  
}
?>
