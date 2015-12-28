<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\presentation;
use fajr\htmlgen\Renderable;
use fajr\libfajr\pub\base\Trace;
use fajr\htmlgen\Table;
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\HtmlHeader;
use fajr\TableDefinitions;
use fajr\Sorter;
use fajr\FajrUtils;
use fajr\Input;

class ZapisanePredmetyCallback implements Renderable
{
  private $skusky;
  
  public function __construct(Trace $trace, $skusky)
  {
    $this->trace = $trace;
    $this->skusky = $skusky;
  }
  
  public function getHtml()
  {
    $trace = $this->trace->addChild("ZapisanePredmetyCallback");;
    $predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu($trace);
    $predmetyZapisnehoListuTable = new
        Table(TableDefinitions::predmetyZapisnehoListu());
    $predmetyZapisnehoListuCollapsible = new Collapsible(
        new HtmlHeader('Predmety zápisného listu'),
        $predmetyZapisnehoListuTable);
    $kreditovCelkomLeto = 0;
    $kreditovCelkomZima = 0;
    $pocetPredmetovLeto = 0;
    $pocetPredmetovZima = 0;
    foreach (Sorter::sort($predmetyZapisnehoListu->getData(),
          array("kodSemester"=>-1, "nazov"=>1)) as $row) {
      if ($row['kodSemester']=='L') {
        $pocetPredmetovLeto += 1;
        $kreditovCelkomLeto += $row['kredit'];
        $class='leto';
      }
      else {
        $pocetPredmetovZima += 1;
        $kreditovCelkomZima += $row['kredit'];
        $class='zima';
      }
      $predmetyZapisnehoListuTable->addRow($row, array('class'=>$class));
    }

    $pocetPredmetovText = 'Celkom ';
    $pocetPredmetovText .= FajrUtils::formatPlural(
        $pocetPredmetovLeto+$pocetPredmetovZima,
        '0 predmetov', '1 predmet', '%d predmety', '%d predmetov');
    if ($pocetPredmetovLeto > 0 && $pocetPredmetovZima > 0) {
      $pocetPredmetovText .= sprintf(' (%d v zime, %d v lete)',
                                     $pocetPredmetovZima, $pocetPredmetovLeto);
    }

    $kreditovCelkomText = ''. ($kreditovCelkomLeto+$kreditovCelkomZima);
    if ($kreditovCelkomLeto > 0 && $kreditovCelkomZima > 0) {
      $kreditovCelkomText .= sprintf(' (%d+%d)',
                                     $kreditovCelkomZima, $kreditovCelkomLeto);
    }

    $predmetyZapisnehoListuTable->addFooter(array('nazov'=>$pocetPredmetovText,
                                                  'kredit'=>$kreditovCelkomText),
                                            array());
    $predmetyZapisnehoListuTable->setUrlParams(array('studium' => Input::get('studium'),
                                                     'list' => Input::get('list')));
    
    return $predmetyZapisnehoListuTable->getHtml();
  }
}
