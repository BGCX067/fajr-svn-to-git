<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre štúdium
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\controller\BaseController;
use fajr\Request;
use fajr\Response;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017; // *
use fajr\PriemeryCalculator;
use fajr\Sorter;
use fajr\libfajr\AIS2Utils;
use fajr\Context;

/**
 * Controller, ktory nacita informacie o aktualnom studiu
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class StudiumController extends BaseController
{

  /** @var VSES017_factory Screen factory to use */
  protected $screenFactory = null;

  /**
   * Invoke an action given its name
   *
   * This function requests information necessary to operate on
   * VSES017 AIS application
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Context $context fajr context
   */
  public function invokeAction(Trace $trace, $action, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();

    Preconditions::checkIsString($action);

    $this->screenFactory = new VSES017\VSES017_factory($context->getAisConnection());
    $this->adminStudia = $this->screenFactory->newAdministraciaStudiaScreen($trace);

    $this->studium = $request->getParameter('studium', '0');

    $this->zoznamStudii = $this->adminStudia->getZoznamStudii(
                                      $trace->addChild("Get Zoznam Studii:"));

    $this->zapisneListy = $this->adminStudia->getZapisneListy(
                                      $trace->addChild('getZapisneListy'),
                                      $this->studium);

    $this->zapisnyList = $request->getParameter('list');

    if (empty($this->zapisnyList)) {
      $tmp = $this->zapisneListy->getData();
      $lastList = end($tmp);
      $this->zapisnyList = $lastList['index'];
    }

    $this->terminyHodnoteniaScreen = $this->screenFactory->newTerminyHodnoteniaScreen(
              $trace,
              $this->adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList),
              $this->adminStudia->getStudiumIdFromZapisnyListIndex($trace, $this->zapisnyList));

    // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
    // objekty, ktore v konstruktore robia requesty
    $this->hodnoteniaScreen = $this->screenFactory->newHodnoteniaPriemeryScreen(
          $trace,
          $this->adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList));

    $response->set('zoznamStudii', $this->zoznamStudii);
    $response->set('studium', $this->studium);
    $response->set('zapisneListy', $this->zapisneListy);
    $response->set('zapisnyList', $this->zapisnyList);    

    parent::invokeAction($trace, $action, $context);
  }

  /**
   * Akcia pre hodnotenia a priemery
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runHodnotenia(Trace $trace, Context $context) {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $this->hodnotenia = $this->hodnoteniaScreen->getHodnotenia($trace);
    $this->priemeryCalculator = new PriemeryCalculator();

    $this->hodnoteniaData = Sorter::sort($this->hodnotenia->getData(),
          array("semester"=>-1, "nazov"=>1));

    foreach($this->hodnoteniaData as $row) {
      if ($row['semester']=='L') {
        $this->priemeryCalculator->add(PriemeryCalculator::SEMESTER_LETNY,
          $row['znamka'], $row['kredit']);
      }
      else {
        $this->priemeryCalculator->add(PriemeryCalculator::SEMESTER_ZIMNY,
          $row['znamka'], $row['kredit']);
      }
    }

    $this->priemery = $this->hodnoteniaScreen->getPriemery($trace);

    $response->set('hodnotenia', $this->hodnoteniaData);
    $response->set('priemery', $this->priemery->getData());
    $response->set('priemeryCalculator', $this->priemeryCalculator);
    $response->setTemplate('studium/hodnotenia');
  }

  /**
   * ked odhlasujeme z predmetu, narozdiel od AISu robime opat
   * inicializaciu vsetkych aplikacii. Just for sure chceme
   * okontrolovat, ze sa nic nezmenilo a ze sme dostali rovnake data
   * ako predtym!
   */
  private function hashNaOdhlasenie($row) {
    return
      md5($row['index'].'|'.$row['datum'].'|'.$row['cas'].'|'.$row['predmet']);
  }

  public function runOdhlasZoSkusky(Trace $trace, Context $context) {

    $request = $context->getRequest();
    $response = $context->getResponse();

    $terminIndex = $request->getParameter("odhlasIndex");

    $terminy = $this->terminyHodnoteniaScreen->getTerminyHodnotenia($this->trace->addChild('get terminy
          hodnotenia '))->getData();

    $terminKey = -1;
    foreach ($terminy as $key=>$row) {
      if ($row['index']==$terminIndex) $terminKey = $key;
    }

    if ($terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }
    if ($request->getParameter("hash") != $this->hashNaOdhlasenie($terminy[$terminKey])) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }

    if (!$this->terminyHodnoteniaScreen->odhlasZTerminu($terminIndex)) {
      throw new Exception('Z termínu sa nepodarilo odhlásiť.');
    }

    FajrUtils::redirect(array('action' => 'studium.MojeTerminyHodnotenia',
                              'studium' => $this->studium,
                              'list' => $this->zapisnyList));
  }

  /**
   * Akcia pre zobrazenie mojich terminov hodnotenia
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function runMojeTerminyHodnotenia(Trace $trace, Context $context) {

    $request = $context->getRequest();
    $response = $context->getResponse();

    $termin = $request->getParameter('termin');

    $this->terminyHodnotenia = $this->terminyHodnoteniaScreen->getTerminyHodnotenia(
        $trace->addChild("get terminy hodnotenia"));
    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia(
        $trace->addChild("get hodnotenia"));

    $hodnoteniePredmetu = array();
    foreach($hodnotenia->getData() as $row) {
      $hodnoteniePredmetu[$row['nazov']] = $row['znamka'];
    }

    $this->terminyHodnoteniaActive = array();
    $this->terminyHodnoteniaOld = array();

    foreach($this->terminyHodnotenia->getData() as $row) {
      $datum = AIS2Utils::parseAISDateTime($row['dat']." ".$row['cas']);

      if ($row['znamka']=="") { // skusme najst znamku v hodnoteniach
        if (isset($hodnoteniePredmetu[$row['predmet']]) &&
              $hodnoteniePredmetu[$row['predmet']]!="") {
            $row['znamka'] =
                $hodnoteniePredmetu[$row['predmet']]." (z&nbsp;predmetu)";
            }
      }

      if ($datum < time()) {
        if ($row['jePrihlaseny']=='A') {
          $this->terminyHodnoteniaOld[] = $row;
        }
      } else {
        if ($row['mozeOdhlasit'] == 1) {
          $row['hashNaOdhlasenie'] = $this->hashNaOdhlasenie($row);
        }

        $this->terminyHodnoteniaActive[] = $row;
      }
    }

    $this->prihlaseni = null;
    $response->set('prihlaseni', null);
    if ($request->getParameter('termin')!=null) {
      $this->prihlaseni = $this->terminyHodnoteniaScreen->
            getZoznamPrihlasenychDialog($trace, $termin)->
              getZoznamPrihlasenych($trace);
      $response->set('prihlaseni', $this->prihlaseni->getData());
    }

    $response->set('terminyActive', $this->terminyHodnoteniaActive);
    $response->set('terminyOld', $this->terminyHodnoteniaOld);
    $response->set('termin', $termin);

    

    $response->setTemplate('studium/mojeTerminyHodnotenia');
  }

  public function runZapisanePredmety(Trace $trace, Context $context) {

    $request = $context->getRequest();
    $response = $context->getResponse();
    
    $predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    
    $this->kreditovCelkomLeto = 0;
    $this->kreditovCelkomZima = 0;
    $this->pocetPredmetovLeto = 0;
    $this->pocetPredmetovZima = 0;

    $this->predmetyZapisnehoListuData = Sorter::sort($predmetyZapisnehoListu->getData(),
          array("kodSemester"=>-1, "nazov"=>1));

    foreach ($this->predmetyZapisnehoListuData as $row) {
      if ($row['kodSemester']=='L') {
        $this->pocetPredmetovLeto += 1;
        $this->kreditovCelkomLeto += $row['kredit'];
      }
      else {
        $this->pocetPredmetovZima += 1;
        $this->kreditovCelkomZima += $row['kredit'];
      }
    }

    $response->set('predmetyZapisnehoListu', $this->predmetyZapisnehoListuData);
    $response->set('kreditovCelkomLeto', $this->kreditovCelkomLeto);
    $response->set('kreditovCelkomZima', $this->kreditovCelkomZima);
    $response->set('pocetPredmetovLeto', $this->pocetPredmetovLeto);
    $response->set('pocetPredmetovZima', $this->pocetPredmetovZima);
    $response->setTemplate('studium/zapisanePredmety');
  }

  private function hashNaPrihlasenie($predmet, $row) {
    return
      md5($row['index'].'|'.$row['dat'].'|'.$row['cas'].'|'.$predmet);

  }

  public function runPrihlasNaSkusku(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $predmetIndex = $request->getParameter("prihlasPredmetIndex");
    $terminIndex = $request->getParameter("prihlasTerminIndex");

    $predmety = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu()->getData();
    $predmetKey = -1;
    foreach ($predmety as $key=>$row) {
      if ($row['index']==$predmetIndex) $predmetKey = $key;
    }

    $terminy =
      $this->terminyHodnoteniaScreen->getZoznamTerminovDialog($predmetIndex)->getZoznamTerminov()->getData();
    $terminKey = -1;
    foreach($terminy as $key=>$row) {
      if ($row['index']==$terminIndex) $terminKey = $key;
    }
    if ($predmetKey == -1 || $terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }

    $hash = $this->hashNaPrihlasenie($predmety[$predmetIndex]['nazov'],
        $terminy[$terminIndex]);
    if ($hash != $request->getParameter('hash')) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }
    if (!$this->terminyHodnoteniaScreen->getZoznamTerminovDialog($predmetIndex)->prihlasNaTermin($terminIndex)) {
      throw new Exception('Na skúšku sa nepodarilo prihlásiť.');
    }

    FajrUtils::redirect(array('action' => 'studium.MojeTerminyHodnotenia',
                              'studium' => $this->studium,
                              'list' => $this->zapisnyList));
  }

  const PRIHLASIT_MOZE = 0;
  const PRIHLASIT_MOZE_ZNAMKA = -1;
  const PRIHLASIT_NEMOZE_CAS = 1;
  const PRIHLASIT_NEMOZE_POCET = 2;
  const PRIHLASIT_NEMOZE_ZNAMKA = 3;
  const PRIHLASIT_NEMOZE_INE = 4;

  /**
   * TODO: toto by malo byt v modeli, nie v controlleri
   * @param <type> $row
   * @return <type>
   */
  private function mozeSaPrihlasit($row) {
    $prihlasRange = AIS2Utils::parseAISDateTimeRange($row['prihlasovanie']);
    $predmet = $row['predmet'];
    if (isset($this->hodnoteniaData[$predmet]['znamka'])) {
      $znamka = $this->hodnoteniaData[$predmet]['znamka'];
    } else {
      $znamka = "";
    }

    if (isset($this->hodnoteniaData[$predmet]['mozePrihlasit']) &&
        $this->hodnoteniaData[$predmet]['mozePrihlasit']=='N') {
      $mozePredmet = false;
    } else {
      $mozePredmet = true;
    }

    if ($znamka!="" && $znamka!="FX" && !$mozePredmet) {
      return self::PRIHLASIT_NEMOZE_ZNAMKA;
    }

    if (!($prihlasRange['od'] < time() && $prihlasRange['do']>time())) {
      return self::PRIHLASIT_NEMOZE_CAS;
    }
    if ($row['maxPocet'] != '' &&
        $row['maxPocet']==$row['pocetPrihlasenych']) {
      return self::PRIHLASIT_NEMOZE_POCET;
    }

    if (!$mozePredmet) {
      return self::PRIHLASIT_NEMOZE_INE;
    }

    if ($znamka!="" && $znamka!="FX" && $mozePredmet) {
      return self::PRIHLASIT_MOZE_ZNAMKA;
    }

    return self::PRIHLASIT_MOZE;
  }

  public function runZoznamTerminov(Trace $trace, Context $context) {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $this->predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    $hodnoteniaData = array();

    foreach ($this->hodnoteniaScreen->getHodnotenia($trace)->getData() as $row) {
      $hodnoteniaData[$row['nazov']]=$row;;
    }
    $this->hodnoteniaData = $hodnoteniaData;

    $this->terminyData = array();
    
    foreach ($this->predmetyZapisnehoListu->getData() as $predmetRow) {

      $dialog = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog(
          $trace->addChild('Get zoznam terminov'), $predmetRow['index']);
      $terminy = $dialog->getZoznamTerminov($trace->addChild('Get zoznam terminov'));
      unset($dialog);

      foreach($terminy->getData() as $row) {
        $row['predmet'] = $predmetRow['nazov'];
        $row['predmetIndex'] = $predmetRow['index'];
        $row['znamka'] = $hodnoteniaData[$row['predmet']]['znamka'];

        $row['hashNaPrihlasenie'] = $this->hashNaPrihlasenie($predmetRow['nazov'], $row);

        $row['mozeSaPrihlasit'] = $this->mozeSaPrihlasit($row);
        
        $this->terminyData[] = $row;
      }
    }

    $this->prihlaseni = null;
    $response->set('prihlaseni', null);
    if ($request->getParameter('termin') != null && $request->getParameter('predmet')!=null) {
      $this->prihlaseni = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog($trace, $request->getParameter('predmet'))
        ->getZoznamPrihlasenychDialog($trace, $request->getParameter('termin'))
        ->getZoznamPrihlasenych($trace);
      $response->set('prihlaseni', $this->prihlaseni->getData());
    }

    $response->set('predmetyZapisnehoListu', $this->predmetyZapisnehoListu);
    $response->set('terminy', $this->terminyData);
    $response->set('termin', $request->getParameter('termin'));
    $response->set('predmet', $request->getParameter('predmet'));

    $response->setTemplate('studium/zoznamTerminov');
  }

}