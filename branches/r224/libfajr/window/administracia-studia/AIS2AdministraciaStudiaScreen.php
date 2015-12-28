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
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @author majak
 */
class AIS2AdministraciaStudiaScreen extends AIS2AbstractScreen
{
  const APP_LOCATION_PATTERN = '@webui\(\)\.startApp\("([^"]+)","([^"]+)"\);@';

  public static function get_tabulka_zoznam_studii() {
    return array( // {{{
      'rocnik',
      'skratka',
      'kruzok',
      'studijnyProgram',
      'doplnujuceUdaje',
      'zaciatokStudia',
      'koniecStudia',
      'dlzkaVSemestroch',
      'dlzkaStudia',
      'cisloDiplomu',
      'cisloZMatriky',
      'cisloVysvedcenia',
      'cisloDodatku',
      'cisloEVI',
      'cisloProgramu',
      'priznak',
      'organizacnaJednotka',
      'rokStudia',
    ); // }}}
  }

  protected $tabulka_zoznam_zapisnych_listov = array(
    // {{{
    'akademickyRok',
    'rocnik',
    'studProgramSkratka',
    'studijnyProgram',
    'doplnujuceUdaje',
    'datumZapisu',
    'potvrdenyZapis',
    'podmienecnyZapis',
    'dlzkaVSemestroch',
    'cisloEVI',
    'cisloProgramu',
    'datumSplnenia',
    'priznak',
    'organizacnaJednotka',
    'typFinacovania',
    'skratkaTypuFinacovania',
    // }}}
  );

  protected $idCache = array();

  public function __construct()
  {
    parent::__construct('ais.gui.vs.es.VSES017App', '&kodAplikacie=VSES017');
  }

  public function getZoznamStudii()
  {
    $this->open();
    $data = match($this->data, AIS2Utils::DATA_PATTERN);
    return new AIS2Table($this->get_tabulka_zoznam_studii(), $data);
  }

  public function getZapisneListy($studiumIndex)
  {
    $this->open();
    $data = $this->requestData(array(
      'compName' => 'nacitatDataAction',
      'objProperties' => array(
        'x' => -4,
        'y' => -4,
        'focusedComponent' => 'nacitatButton',
      ),
      'embObj' => array(
        'objName' => 'studiaTable',
        'dataView' => array(
          'activeIndex' => $studiumIndex,
          'selectedIndexes' => $studiumIndex,
        ),
      ),
    ));
    
    $data = match($data, AIS2Utils::DATA_PATTERN);
    return new AIS2Table($this->tabulka_zoznam_zapisnych_listov, $data);
  }

  public function getIdZapisnyList($zapisnyListIndex)
  {
    return $this->getIdFromZapisnyListIndex($zapisnyListIndex, 'idZapisnyList');
  }

  public function getIdStudium($zapisnyListIndex)
  {
    return $this->getIdFromZapisnyListIndex($zapisnyListIndex, 'idStudium');
  }

  protected function getIdFromZapisnyListIndex($zapisnyListIndex, $idType)
  {
    $this->open();
    if (empty($this->idCache[$zapisnyListIndex]))
    {
      $response = $this->requestData(array(
        'compName' => 'terminyHodnoteniaAction',
        'objProperties' => array(
          'x' => -4,
          'y' => -4,
          'focusedComponent' => 'runZapisneListyButton',
        ),
        'embObj' => array(
          'objName' => 'zoznamTemTable',
          'dataView' => array(
            'activeIndex' => $zapisnyListIndex,
            'selectedIndexes' => $zapisnyListIndex,
          ),
        ),
      ));
      $data = $this->parseIdFromZapisnyListIndexFromResponse($response);
      if ($data == null) {
        throw new Exception("Neviem parsovať dáta z AISu");
      }
    
      $this->idCache[$zapisnyListIndex] = $data;
    }
    return $this->idCache[$zapisnyListIndex][$idType];
  }

  public function parseIdFromZapisnyListIndexFromResponse($response) {
      // FIXME: toto tunak spravit nejak krajsie
      $data = matchAll($response, self::APP_LOCATION_PATTERN, true);
      if ($data === false) return null;
      $data = matchAll($data[2], '@&idZapisnyList\=(?P<idZapisnyList>[0-9]*)&idStudium\=(?P<idStudium>[0-9]*)@', true);
      if ($data === false) return null;
      return removeIntegerIndexesFromArray($data);
  }

}
?>
