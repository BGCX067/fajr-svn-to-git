<?php
/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre štúdium
 *
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\controller\studium\PriemeryCalculator;
use fajr\libfajr\AIS2Utils;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\window\AIS2ApplicationEnum;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017;
use fajr\libfajr\pub\regression;
use fajr\Request;
use fajr\Response;
use fajr\Sorter;
use fajr\util\FajrUtils;

fields::autoload();

/**
 * Controller, ktory nacita informacie o aktualnom studiu
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class StudiumController extends BaseController
{
  // @input
  private $studium;
  private $zapisnyList;

  // @private
  private $zoznamStudii;
  private $zapisneListy;
  private $terminyHodnoteniaScreen;
  private $hodnoteniaScreen;
  private $administraciaStudiaScreen;

  private $factory;
  private $serverTime;
  private $actionInfo;

  public function __construct(VSES017\VSES017_Factory $factory, $serverTime)
  {
    $this->factory = $factory;
    $this->serverTime = $serverTime;
    $this->actionInfo = array('MojeTerminyHodnotenia' => array('tabName' => 'TerminyHodnotenia', 'requiresZapisnyList' => true),
                              'ZoznamTerminov' => array('tabName' => 'ZapisSkusok', 'requiresZapisnyList' => true),
                              'ZapisanePredmety' => array('tabName' => 'ZapisnyList', 'requiresZapisnyList' => true),
                              'Hodnotenia' => array('tabName' => 'Hodnotenia', 'requiresZapisnyList' => true),
                              'PrehladKreditov' => array('tabName' => 'PrehladKreditov', 'requiresZapisnyList' => false),
                              );
  }

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
    Preconditions::checkIsString($action);

    $request = $context->getRequest();
    $response = $context->getResponse();
    $session = $context->getSessionStorage();
    Preconditions::checkNotNull($request);
    Preconditions::checkNotNull($response);
    Preconditions::checkNotNull($session);
    // check access to application
    $apps = $session->read('ais/aisApps');
    if (!is_array($apps)) {
      throw new Exception("Interná chyba - zoznam AIS aplikácii je nekorektný.");
    }
    if (!in_array(AIS2ApplicationEnum::ADMINISTRACIA_STUDIA,
                  $apps)) {
      $response->setTemplate('studium/notAvailable');
      return;
    }

    $screenFactory = $this->factory;
    $adminStudia = $screenFactory->newAdministraciaStudiaScreen($trace);
    
    $this->administraciaStudiaScreen = $adminStudia;

    $this->studium = $request->getParameter('studium', '0');

    $this->zoznamStudii = $adminStudia->getZoznamStudii(
                                      $trace->addChild("Get Zoznam Studii:"));

    $this->zapisneListy = $adminStudia->getZapisneListy(
                                      $trace->addChild('getZapisneListy'),
                                      $this->studium);

    FajrUtils::warnWrongTableStructure($trace, $response, 'zoznam studii',
        regression\ZoznamStudiiRegression::get(),
        $this->zoznamStudii->getTableDefinition());

    FajrUtils::warnWrongTableStructure($trace, $response, 'zoznam zapisnych listov',
        regression\ZoznamZapisnychListovRegression::get(),
        $this->zapisneListy->getTableDefinition());

    $zapisneListyData = $this->zapisneListy->getData();
    if (count($zapisneListyData) == 0) {
      $this->zapisnyList = null;
      $this->terminyHodnoteniaScreen = null;
      $this->hodnoteniaScreen = null;
    }
    else {
      $this->zapisnyList = $request->getParameter('list');

      if ($this->zapisnyList === '') {
        $lastList = end($zapisneListyData);
        $this->zapisnyList = $lastList['index'];
      }

      $this->terminyHodnoteniaScreen = $screenFactory->newTerminyHodnoteniaScreen(
                $trace,
                $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList,
                    VSES017\AdministraciaStudiaScreen::ACTION_TERMINY_HODNOTENIA),
                $adminStudia->getStudiumIdFromZapisnyListIndex($trace, $this->zapisnyList,
                    VSES017\AdministraciaStudiaScreen::ACTION_TERMINY_HODNOTENIA));

      // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
      // objekty, ktore v konstruktore robia requesty
      $this->hodnoteniaScreen = $screenFactory->newHodnoteniaPriemeryScreen(
            $trace,
            $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList,
                VSES017\AdministraciaStudiaScreen::ACTION_HODNOTENIA_PRIEMERY));
    }

    $response->set('zoznamStudii', $this->zoznamStudii);
    $response->set('studium', $this->studium);
    $response->set('zapisneListy', $this->zapisneListy);
    $response->set('zapisnyList', $this->zapisnyList);

    if (array_key_exists($action, $this->actionInfo)) {
      $info = $this->actionInfo[$action];
      if ($info['requiresZapisnyList'] && $this->zapisnyList == null) {
        $response->set('activeTab', $info['tabName']);
        $response->setTemplate('studium/chybaZapisnyList');
        return;
      }
    }

    parent::invokeAction($trace, $action, $context);
  }
  
  public function runPrehladKreditov(Trace $trace, Context $context) {
    $response = $context->getResponse();
    
    $prehladKreditovDialog = $this->administraciaStudiaScreen->
        getPrehladKreditovDialog($trace, $this->studium);
    
    $predmety = $prehladKreditovDialog->getPredmety($trace);
    
    $prehladKreditovDialog->closeIfNeeded($trace);
    
    FajrUtils::warnWrongTableStructure($trace, $response, 'prehlad kreditov',
        regression\PrehladKreditovRegression::get(),
        $predmety->getTableDefinition());
    
    $predmetyData = $hodnoteniaData = Sorter::sort($predmety->getData(),
          array("akRok"=>1, "semester"=>-1, "nazov"=>1));
    
    $response->set('predmety', $predmetyData);
    $response->setTemplate('studium/prehladKreditov');
  }

  /**
   * Akcia pre hodnotenia a priemery
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runHodnotenia(Trace $trace, Context $context) {
    $priemeryCalculator = new PriemeryCalculator();
    $request = $context->getRequest();
    $response = $context->getResponse();

    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia($trace->addChild('get hodnotenia'));

    FajrUtils::warnWrongTableStructure($trace, $response, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());

    $hodnoteniaData = Sorter::sort($hodnotenia->getData(),
          array("semester"=>-1, "nazov"=>1));

    foreach($hodnoteniaData as $hodnoteniaRow) {
      $semester = $hodnoteniaRow[HodnoteniaFields::SEMESTER] == 'L' ?
          PriemeryCalculator::SEMESTER_LETNY : PriemeryCalculator::SEMESTER_ZIMNY;

      $priemeryCalculator->add($semester,
                               $hodnoteniaRow[HodnoteniaFields::ZNAMKA],
                               $hodnoteniaRow[HodnoteniaFields::KREDIT]);
    }

    $priemery = $this->hodnoteniaScreen->getPriemery($trace->addChild('get priemery'));

    FajrUtils::warnWrongTableStructure($trace, $response, 'priemery',
        regression\PriemeryRegression::get(),
        $priemery->getTableDefinition());

    $response->set('hodnotenia', $hodnoteniaData);
    $response->set('priemery', $priemery->getData());
    $response->set('priemeryCalculator', $priemeryCalculator);
    $response->setTemplate('studium/hodnotenia');
  }


  /**
   * Akcia ktora odhlasi cloveka z danej skusky
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runOdhlasZoSkusky(Trace $trace, Context $context) {

    $request = $context->getRequest();
    $response = $context->getResponse();

    $terminIndex = $request->getParameter("odhlasIndex");

    $terminy = $this->terminyHodnoteniaScreen
        ->getTerminyHodnotenia($trace->addChild('get terminy hodnotenia '));
    FajrUtils::warnWrongTableStructure($trace, $response, 'moje terminy',
        regression\MojeTerminyRegression::get(),
        $terminy->getTableDefinition());

    $terminyData = $terminy->getData();

    $terminKey = -1;
    foreach ($terminyData as $key=>$row) {
      if ($row['index'] == $terminIndex) $terminKey = $key;
    }

    if ($terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }
    if ($request->getParameter("hash") !== StudiumUtils::hashNaOdhlasenie($terminyData[$terminKey])) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }

    if (!$this->terminyHodnoteniaScreen->odhlasZTerminu($trace->addChild('odhlasujem'), $terminIndex)) {
      throw new Exception('Z termínu sa nepodarilo odhlásiť.');
    }

    $response->redirect(array('action' => 'studium.MojeTerminyHodnotenia',
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

    $terminyHodnotenia = $this->terminyHodnoteniaScreen->getTerminyHodnotenia(
        $trace->addChild("get terminy hodnotenia"));
    FajrUtils::warnWrongTableStructure($trace, $response, 'moje terminy hodnotenia',
        regression\MojeTerminyRegression::get(),
        $terminyHodnotenia->getTableDefinition());

    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia(
        $trace->addChild("get hodnotenia"));
    FajrUtils::warnWrongTableStructure($trace, $response, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());

    $hodnoteniePredmetu = array();
    foreach($hodnotenia->getData() as $hodnoteniaRow) {
      $hodnoteniePredmetu[$hodnoteniaRow[HodnoteniaFields::PREDMET_SKRATKA]] =
            $hodnoteniaRow[HodnoteniaFields::ZNAMKA];
    }

    $terminyHodnoteniaActive = array();
    $terminyHodnoteniaOld = array();

    foreach($terminyHodnotenia->getData() as $terminyRow) {
      $mojeTerminyRow = $terminyRow;

      if ($terminyRow[TerminyFields::ZNAMKA] == '') { // skusme najst znamku v hodnoteniach
        $predmet = $terminyRow[TerminyFields::PREDMET_SKRATKA];
        if (isset($hodnoteniePredmetu[$predmet]) &&
              $hodnoteniePredmetu[$predmet] != '' ) {
            // TODO(ppershing): move this to another field and deal with it in template!
            $mojeTerminyRow[TerminyFields::ZNAMKA] =
                $hodnoteniePredmetu[$predmet]." (nepriradená k termínu)";
            }
      }

      $datum = AIS2Utils::parseAISDateTime($terminyRow[TerminyFields::DATUM]." ".$terminyRow[TerminyFields::CAS]);
      if ($datum < $this->serverTime) {
        if ($terminyRow[TerminyFields::JE_PRIHLASENY]=='TRUE') {
          $terminyHodnoteniaOld[] = $mojeTerminyRow;
        }
      } else {
        if ($terminyRow[TerminyFields::MOZE_ODHLASIT] == 1) {
          $mojeTerminyRow[MojeTerminyFields::HASH_ODHLASENIE] = StudiumUtils::hashNaOdhlasenie($terminyRow);
        }

        $terminyHodnoteniaActive[] = $mojeTerminyRow;
      }
    }

    $response->set('prihlaseni', null);
    if ($request->getParameter('termin') !== '') {
      $prihlaseni = $this->terminyHodnoteniaScreen->
            getZoznamPrihlasenychDialog($trace, $termin)->
              getZoznamPrihlasenych($trace);
      FajrUtils::warnWrongTableStructure($trace, $response, 'prihlaseni na termin',
          regression\PrihlaseniNaTerminRegression::get(),
          $prihlaseni->getTableDefinition());
      $response->set('prihlaseni', $prihlaseni->getData());
    }

    $response->set('terminyActive', $terminyHodnoteniaActive);
    $response->set('terminyOld', $terminyHodnoteniaOld);
    $response->set('termin', $termin);

    $response->setTemplate('studium/mojeTerminyHodnotenia');
  }

  /**
   * Akcia ktora zobrazi predmety zapisane danym clovekom
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runZapisanePredmety(Trace $trace, Context $context) {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    FajrUtils::warnWrongTableStructure($trace, $response, 'terminy hodnotenia-predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmetyZapisnehoListu->getTableDefinition());

    $priemeryCalculator = new PriemeryCalculator();

    $predmetyZapisnehoListuData = Sorter::sort($predmetyZapisnehoListu->getData(),
          array("kodSemester"=>-1, "nazov"=>1));

    foreach ($predmetyZapisnehoListuData as $predmetyRow) {
      $semester = $predmetyRow[PredmetyFields::SEMESTER] == 'L' ?
          PriemeryCalculator::SEMESTER_LETNY : PriemeryCalculator::SEMESTER_ZIMNY;
      $priemeryCalculator->add($semester, '', $predmetyRow[PredmetyFields::KREDIT]);
    }

    $response->set('predmetyZapisnehoListu', $predmetyZapisnehoListuData);
    $response->set('predmetyStatistika', $priemeryCalculator);
    $response->setTemplate('studium/zapisanePredmety');
  }

  /**
   * Akcia ktora sa pokusi prihlasit cloveka na danu skusku
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runPrihlasNaSkusku(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $predmetIndex = $request->getParameter("prihlasPredmetIndex");
    $terminIndex = $request->getParameter("prihlasTerminIndex");


    $predmety = $this->terminyHodnoteniaScreen
          ->getPredmetyZapisnehoListu($trace->addChild('Predmety zapisneho listu'));
    FajrUtils::warnWrongTableStructure($trace, $response, 'terminy hodnotenia - predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmety->getTableDefinition());

    $predmetyData = $predmety->getData();

    $predmetKey = -1;
    foreach ($predmetyData as $key=>$row) {
      if ((string) $row[PredmetyFields::INDEX] === $predmetIndex) {
        $predmetKey = $key;
      }
    }

    $childTrace = $trace->addChild('Zoznam terminov');
    $terminyDialog = $this->terminyHodnoteniaScreen
        ->getZoznamTerminovDialog($childTrace, $predmetIndex);

    $terminy = $terminyDialog->getZoznamTerminov($childTrace);
    FajrUtils::warnWrongTableStructure($trace, $response, 'zoznam mojich terminov',
        regression\MojeTerminyRegression::get(),
        $terminy->getTableDefinition());

    $terminyData = $terminy->getData();
    $terminKey = -1;
    foreach($terminyData as $key=>$terminyRow) {
      if ((string) $terminyRow[TerminyFields::INDEX] === $terminIndex) {
        $terminKey = $key;
      }
    }

    if ($predmetKey == -1 || $terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }

    $hash = StudiumUtils::hashNaPrihlasenie($predmetyData[$predmetKey][PredmetyFields::SKRATKA],
                                     $terminyData[$terminIndex]);
    if ($hash != $request->getParameter('hash')) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }
    if (!$terminyDialog->prihlasNaTermin($trace->addChild('prihlasujem na termin'), $terminIndex)) {
      throw new Exception('Na skúšku sa nepodarilo prihlásiť.');
    }

    $response->redirect(array('action' => 'studium.MojeTerminyHodnotenia',
                              'studium' => $this->studium,
                              'list' => $this->zapisnyList));
  }


  /**
   * Akcia ktora zobrazi terminy, na ktore je mozne potencialne sa prihlasit.
   *
   * @param Trace $trace trace object
   * @param Context $context
   */
  public function runZoznamTerminov(Trace $trace, Context $context) {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    FajrUtils::warnWrongTableStructure($trace, $response, 'terminy hodnotenia - predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmetyZapisnehoListu->getTableDefinition());

    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia($trace);
    FajrUtils::warnWrongTableStructure($trace, $response, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());
    $hodnoteniaData = array();

    foreach ($hodnotenia->getData() as $row) {
      $hodnoteniaData[$row[HodnoteniaFields::PREDMET_SKRATKA]] = $row;
    }

    $mozePrihlasitHelper = new MozePrihlasitNaTerminHelper($hodnoteniaData);

    $terminyData = array();

    foreach ($predmetyZapisnehoListu->getData() as $predmetRow) {
      $predmetSkratka = $predmetRow[PredmetyFields::SKRATKA];
      $predmetId = $predmetRow[PredmetyFields::INDEX];
      $predmet = $predmetRow[PredmetyFields::NAZOV];

      $childTrace = $trace->addChild('Zoznam terminov k predmetu ' . $predmet);
      $dialog = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog(
          $childTrace, $predmetId);
      $terminy = $dialog->getZoznamTerminov($childTrace);
      FajrUtils::warnWrongTableStructure($trace, $response, 'zoznam terminov k predmetu ' . $predmet,
          regression\TerminyKPredmetuRegression::get(),
          $terminy->getTableDefinition());
      // explicitly close this dialog otherwise we will be blocked for next iteration!
      $dialog->closeIfNeeded($childTrace);

      foreach($terminy->getData() as $row) {
        $prihlasTerminyRow = $row;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET] = $predmet;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET_INDEX] = $predmetId;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET_SKRATKA] = $predmetSkratka;
        $prihlasTerminyRow[PrihlasTerminyFields::ZNAMKA] = $hodnoteniaData[$predmetSkratka][HodnoteniaFields::ZNAMKA];

        $prihlasTerminyRow[PrihlasTerminyFields::HASH_PRIHLASENIE] =
            StudiumUtils::hashNaPrihlasenie($predmetSkratka, $row);

        // PrihlasTerminyFields::ZNAMKA, PREDMET_SKRATKA must be set before!
        $prihlasTerminyRow[PrihlasTerminyFields::FAJR_MOZE_PRIHLASIT] =
          $mozePrihlasitHelper->mozeSaPrihlasit($prihlasTerminyRow, $this->serverTime);

        $terminyData[] = $prihlasTerminyRow;
      }
    }

    $response->set('prihlaseni', null);
    if ($request->getParameter('termin') !== '' &&
        $request->getParameter('predmet') !== '') {
      $prihlaseni = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog($trace, $request->getParameter('predmet'))
        ->getZoznamPrihlasenychDialog($trace, $request->getParameter('termin'))
        ->getZoznamPrihlasenych($trace);
      FajrUtils::warnWrongTableStructure($trace, $response, 'zoznam prihlasenych k terminu',
          regression\PrihlaseniNaTerminRegression::get(),
          $prihlaseni->getTableDefinition());
      $response->set('prihlaseni', $prihlaseni->getData());
    }

    $response->set('predmetyZapisnehoListu', $predmetyZapisnehoListu);
    $response->set('terminy', $terminyData);
    $response->set('termin', $request->getParameter('termin'));
    $response->set('predmet', $request->getParameter('predmet'));

    $response->setTemplate('studium/zoznamTerminov');
  }
}
