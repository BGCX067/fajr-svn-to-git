<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\AIS2Utils;
use fajr\libfajr\base\DisableEvilCallsObject;

include_once 'fields.php';

/**
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 *
 * TODO: toto by malo byt v modeli, nie v controlleri
 */
class MozePrihlasitNaTerminHelper extends DisableEvilCallsObject
{
  const PRIHLASIT_MOZE = 0;
  const PRIHLASIT_MOZE_ZNAMKA = -1;
  const PRIHLASIT_NEMOZE_CAS = 1;
  const PRIHLASIT_NEMOZE_POCET = 2;
  const PRIHLASIT_NEMOZE_ZNAMKA = 3;
  const PRIHLASIT_NEMOZE_INE = 4;

  /**
   * array(string(kod predmetu) => array(hodnotenieRow))
   */
  private $hodnoteniaData;

  public function __construct(array $hodnoteniaData)
  {
    $this->hodnoteniaData = $hodnoteniaData;
  }

  public function mozeSaPrihlasit($prihlasTerminyRow, $time)
  {
    $predmet = $prihlasTerminyRow[PrihlasTerminyFields::PREDMET_SKRATKA];
    if (isset($this->hodnoteniaData[$predmet][HodnoteniaFields::ZNAMKA])) {
      $znamka = $this->hodnoteniaData[$predmet][HodnoteniaFields::ZNAMKA];
    } else {
      $znamka = "";
    }

    if (isset($this->hodnoteniaData[$predmet][HodnoteniaFields::MOZE_PRIHLASIT]) &&
        $this->hodnoteniaData[$predmet][HodnoteniaFields::MOZE_PRIHLASIT]=='N') {
      $mozePredmet = false;
    } else {
      $mozePredmet = true;
    }

    if ($znamka!="" && $znamka!="FX" && !$mozePredmet) {
      return self::PRIHLASIT_NEMOZE_ZNAMKA;
    }

    $prihlasRange = AIS2Utils::parseAISDateTimeRange(
        $prihlasTerminyRow[PrihlasTerminyFields::PRIHLASOVANIE_DATUM]);
    if (!($prihlasRange['od'] < $time && $prihlasRange['do']>$time)) {
      return self::PRIHLASIT_NEMOZE_CAS;
    }
    if (($prihlasTerminyRow[PrihlasTerminyFields::MAX_POCET] !== '') &&
        ($prihlasTerminyRow[PrihlasTerminyFields::MAX_POCET] <=
         $prihlasTerminyRow[PrihlasTerminyFields::POCET_PRIHLASENYCH])) {
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

}
