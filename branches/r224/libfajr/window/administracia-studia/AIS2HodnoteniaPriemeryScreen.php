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
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @author majak
 */
class AIS2HodnoteniaPriemeryScreen extends AIS2AbstractScreen
{
  public static function get_tabulka_hodnotenia() {
    return array( // {{{
      'semester',
      'kodCastSP',
      'kodTypVyucbySP',
      'skratka',
      'nazov',
      'kredit',
      'kodSposUkon',
      'termin',
      'znamka',
      'datum',
      'uznane',
      'blokPopis',
      'poplatok',
      'nahradzaMa',
      'nahradzam',
      'znamkaPopis',
      'dovezene',
      'mozePrihlasit',
      'rozsah',
      'priebHodn',
    ); // }}}
  }

  protected $tabulka_priemery = array(
    // {{{
    'priemerInfoPopisAkadRok',
    'priemerInfoKodSemester',
    'vazPriemer',
    'studPriemer',
    'pocetPredmetov',
    'pocetNeabs',
    'pokusyPriemer',
    'ziskanyKredit',
    'prerusUkon',
    'priemerInfoDatum',
    'priemerInfoDatum1Hodn',
    'priemerInfoDatum2Hodn',
    'priemerNazov',
    'priemerZaAkRok',
    'priemerZaSemester',
    'priemerLenStudPlan',
    'priemerUznanePredm',
    'priemerAjDatum1Hodn',
    'priemerAjDatum2Hodn',
    'priemerPocitatNeabs',
    'priemerVahaNeabsolvovanych',
    'priemerSkratkaOrganizacnaJednotka',
    'priemerPocitatNeabsC',
    'pocetPredmetovVyp',
    'priemerInfoStudentiVypoctu',
    // }}}
  );

  public function __construct($idZapisnyList)
  {
    parent::__construct('ais.gui.vs.es.VSES212App', '&kodAplikacie=VSES212&idZapisnyList='.$idZapisnyList);
  }

  public function getHodnotenia()
  {
    $this->open();
    $data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
    return new AIS2Table($this->get_tabulka_hodnotenia(), $data[0][1]);
  }

  public function getPriemery()
  {
    $this->open();
    $data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
    return new AIS2Table($this->tabulka_priemery, $data[1][1]);
  }

}

?>
