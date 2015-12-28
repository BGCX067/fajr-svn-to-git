<?php
/**
 * Obsahuje nastroje pre pocitanie zakladnych statistik so znamkami
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\data_manipulation\Znamka;
use InvalidArgumentException;

/**
 * Reprezentuje jednu jednotku pre ktoru sa da pocitat statistika (semester,rok)
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class PriemeryInternal
{
  protected $sucet = 0;
  protected $sucetVah = 0;
  protected $pocetPredmetovOhodnotenych = 0;
  protected $pocetKreditovOhodnotenych = 0;
  protected $pocetPredmetovNeohodnotenych = 0;
  protected $pocetKreditovNeohodnotenych = 0;

  /**
   * Zarata znamku
   * @param float $hodnota hodnota znamky, ktora ma byt zaratana
   * @param float $kredity prislusna kreditova hodnota pre pocitanie vaz. priem.
   */
  private function addOhodnotene($hodnota, $kredity)
  {
    $this->sucet += $hodnota;
    $this->sucetVah += $hodnota*$kredity;
    $this->pocetPredmetovOhodnotenych += 1;
    $this->pocetKreditovOhodnotenych += $kredity;
  }

  /**
   * Zarata predmet, pre ktory este nevieme znamku
   * @param float $kredity kreditova hodnota daneho predmetu
   */
  private function addNeohodnotene($kredity)
  {
    $this->pocetPredmetovNeohodnotenych += 1;
    $this->pocetKreditovNeohodnotenych += $kredity;
  }

  /**
   * Zarata predmet s danou znamkou
   * @param float $kredity pocet kreditov, ktore sa maju zaratat
   * @param Znamka $znamka znamka, ktora sa ma zarat, NULL sa rata ako
   *                       neohodnoteny predmet
   * @throws InvalidArgumentException ak dana znamka nie je platna
   */
  public function add($kredity, Znamka $znamka = null)
  {
    Preconditions::checkContainsInteger($kredity);
    Preconditions::check($kredity >= 0, "Kreditov musí byť nezáporný počet.");

    if ($znamka == null) {
      $this->addNeohodnotene($kredity);
      return;
    }

    $this->addOhodnotene($znamka->getNumerickaHodnota(), $kredity);
  }

  /**
   * Vypocita studijny priemer (t.j. priemer z danych znamok)
   * @param boolean $neohodnotene true ak sa maju zaratat aj neohodnotene
   *                              predmety so znamkou Fx
   * @returns float hodnota studijneho priemeru
   */
  public function studijnyPriemer($neohodnotene = true)
  {
    $suma = $this->sucet;
    $pocet = $this->pocetPredmetovOhodnotenych;

    if ($neohodnotene) {
      $hodnotaFx = Znamka::fromString('Fx')->getNumerickaHodnota();
      $suma += $this->pocetPredmetovNeohodnotenych * $hodnotaFx;
      $pocet += $this->pocetPredmetovNeohodnotenych;
    }

    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  /**
   * Vypocita vazeny priemer, vaha sa berie z kreditov
   * @param boolean $neohodnotene true ak sa maju zapocitat aj neohodnotene
   *                              predmety so znamkou Fx
   * @returns float hodnota vazeneho priemeru
   */
  public function vazenyPriemer($neohodnotene=true)
  {
    $suma = $this->sucetVah;
    $pocet = $this->pocetKreditovOhodnotenych;
    if ($neohodnotene) {
      $hodnotaFx = Znamka::fromString('Fx')->getNumerickaHodnota();
      $suma += $this->pocetKreditovNeohodnotenych * $hodnotaFx;
      $pocet += $this->pocetKreditovNeohodnotenych;
    }
    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  /**
   * @returns boolean true ak tento objekt obsahuje nejake data
   */
  public function hasPriemer()
  {
    return $this->pocetPredmetovOhodnotenych > 0;
  }

  /**
   * Vrati celkovy pocet zaratanych kreditov
   * @returns int pocet kreditov celkom
   */
  public function kreditovCelkom()
  {
    return $this->pocetKreditovOhodnotenych + $this->pocetKreditovNeohodnotenych;
  }

  /**
   * Vrati celkovy pocet predmetov
   * @returns int pocet predmetov celkom
   */
  public function predmetovCelkom()
  {
    return $this->pocetPredmetovOhodnotenych + $this->pocetPredmetovNeohodnotenych;
  }

}

/**
 * Pocita statistiky so znamkami pre rozne obdobia
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
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

  /**
   * Prida predmet s danou znamkou, zarata do neohodnotenych ak
   * sa znamku nepodarilo rozpoznat alebo nie je vyplnena
   * @param string $castRoka do ktorej casti roka sa ma znamka zaratat
   * @param string $znamkaText nazov znamky (A, B, ...)
   * @param int $kredity pocet kreditov pre danu znamku
   */
  public function add($castRoka, $znamkaText, $kredity)
  {
    Preconditions::check(in_array($castRoka,
                            array(self::SEMESTER_LETNY,
                                  self::SEMESTER_ZIMNY,
                                  self::AKADEMICKY_ROK)),
                         "Neplatná časť študijného roka.");
    $znamka = null;
    if ($znamkaText !== '') {
      $znamka = Znamka::fromString($znamkaText);
    }
    
    $this->obdobia[$castRoka]->add($kredity, $znamka);
    // Ak pridavame do akademickeho roka, tak hodnotu nechceme zaratat dvakrat
    if ($castRoka !== self::AKADEMICKY_ROK) {
      $this->obdobia[self::AKADEMICKY_ROK]->add($kredity, $znamka);
    }
  }

  /**
   * @returns boolean true ak mame nejake data
   */
  public function hasPriemer()
  {
    return $this->obdobia[self::AKADEMICKY_ROK]->hasPriemer();
  }

  /**
   * Vrati pole s jednotlivymi obdobiami (zima, leto, rok)
   * @returns array(string=>PriemeryInternal)
   */
  public function getObdobia() {
    return $this->obdobia;
  }

}
