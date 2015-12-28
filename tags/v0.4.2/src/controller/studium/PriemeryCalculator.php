<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT licence that can be
// found in the LICENCE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\DisableEvilCallsObject;
use InvalidArgumentException;

class PriemeryInternal extends DisableEvilCallsObject
{
  protected $sucet = 0;
  protected $sucetVah = 0;
  protected $pocetPredmetovOhodnotenych = 0;
  protected $pocetKreditovOhodnotenych = 0;
  protected $pocetPredmetovNeohodnotenych = 0;
  protected $pocetKreditovNeohodnotenych = 0;

  protected static $numerickaHodnotaZnamky = array(
      'A'=>1.0,
      'B'=>1.5,
      'C'=>2.0,
      'D'=>2.5,
      'E'=>3.0,
      'Fx'=>4.0
    );

  private function addOhodnotene($hodnota, $kredity)
  {
    $this->sucet += $hodnota;
    $this->sucetVah += $hodnota*$kredity;
    $this->pocetPredmetovOhodnotenych += 1;
    $this->pocetKreditovOhodnotenych += $kredity;
  }

  private function addNeohodnotene($kredity)
  {
    $this->pocetPredmetovNeohodnotenych += 1;
    $this->pocetKreditovNeohodnotenych += $kredity;
  }

  public function add($znamka, $kredity)
  {
    if (isset(PriemeryInternal::$numerickaHodnotaZnamky[$znamka])) {
      $hodnota = PriemeryInternal::$numerickaHodnotaZnamky[$znamka];
      $this->addOhodnotene($hodnota, $kredity);
    }
    else if ($znamka === '') {
      $this->addNeohodnotene($kredity);
    } else {
      throw new InvalidArgumentException("Známka '$znamka' nie je platná");
    }
  }

  public function studijnyPriemer($neohodnotene = true)
  {
    $suma = $this->sucet;
    $pocet = $this->pocetPredmetovOhodnotenych;

    if ($neohodnotene) {
      $suma += $this->pocetPredmetovNeohodnotenych * self::$numerickaHodnotaZnamky['Fx'];
      $pocet += $this->pocetPredmetovNeohodnotenych;
    }

    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  public function vazenyPriemer($neohodnotene=true)
  {
    $suma = $this->sucetVah;
    $pocet = $this->pocetKreditovOhodnotenych;
    if ($neohodnotene) {
      $suma += $this->pocetKreditovNeohodnotenych * self::$numerickaHodnotaZnamky['Fx'];
      $pocet += $this->pocetKreditovNeohodnotenych;
    }
    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  public function hasPriemer()
  {
    return $this->pocetPredmetovOhodnotenych > 0;
  }

  public function kreditovCelkom()
  {
    return $this->pocetKreditovOhodnotenych + $this->pocetKreditovNeohodnotenych;
  }

  public function predmetovCelkom()
  {
    return $this->pocetPredmetovOhodnotenych + $this->pocetPredmetovNeohodnotenych;
  }

}

class PriemeryCalculator
{
  const SEMESTER_LETNY = 'leto';
  const SEMESTER_ZIMNY = 'zima';
  const AKADEMICKY_ROK = 'rok';

  protected $obdobia = null;

  public function __construct()
  {
    $this->obdobia = array(
        self::SEMESTER_LETNY => new PriemeryInternal(),
        self::SEMESTER_ZIMNY => new PriemeryInternal(),
        self::AKADEMICKY_ROK => new PriemeryInternal()
      );
  }

  public function add($castRoka, $znamka, $kredity)
  {
    $this->obdobia[$castRoka]->add($znamka, $kredity);
    $this->obdobia[self::AKADEMICKY_ROK]->add($znamka, $kredity);
  }

  public function hasPriemer()
  {
    return $this->obdobia[self::AKADEMICKY_ROK]->hasPriemer();
  }

  public function getObdobia() {
    return $this->obdobia;
  }

}
