<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentacia znamky (A, B, C, D, E, Fx)
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\data_manipulation;

use fajr\libfajr\base\Preconditions;
/**
 * Reprezentacia znamky (A, B, C, D, E, Fx)
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class Znamka {
  
  /** var float numericka hodnota pouzivana napriklad pri vypoctoch priemeru */
  private $numerickaHodnota;

  /** var string kratky textovy nazov znamky, napr 'A', 'Fx' */
  private $nazov;

  /** var string slovny popis znamky napriklad 'vyborne' */
  private $slovnyPopis;
  
  /** var string dlhy popis znamky napriklad 'vyzaduje sa dalsia praca' */
  private $dlhyPopis;

  /** var array(string => Znamka) znamky */
  private static $znamky = null;

  private function __construct($nazov, $numerickaHodnota, $slovnyPopis, $dlhyPopis)
  {
    Preconditions::checkIsString($nazov, '$nazov znamky musi byt retazec');
    Preconditions::checkIsNumber($numerickaHodnota,
                                 '$numerickaHodnota znamky musi byt cislo');
    Preconditions::checkIsString($slovnyPopis, '$slovnyPopis znamky musi byt retazec');
    Preconditions::checkIsString($dlhyPopis, '$dlhyPopis znamky musi byt retazec');
    $this->nazov = $nazov;
    $this->numerickaHodnota = $numerickaHodnota;
    $this->slovnyPopis = $slovnyPopis;
    $this->dlhyPopis = $dlhyPopis;
  }

  private static function initZnamkyIfNeeded()
  {
    if (self::$znamky !== null) return;
    self::$znamky = array(
      'A' =>  new Znamka('A',  1.0, 'výborne', 'vynikajúce výsledky'),
      'B' =>  new Znamka('B',  1.5, 'veľmi dobre', 'nadpriemerné výsledky'),
      'C' =>  new Znamka('C',  2.0, 'dobre', 'priemerné výsledky'),
      'D' =>  new Znamka('D',  2.5, 'uspokojivo', 'prijateľné výsledky'),
      'E' =>  new Znamka('E',  3.0, 'dostatočne', 'výsledky spĺňajú minimálne kritériá'),
      'FX' => new Znamka('Fx', 4.0, 'nedostatočne', 'vyžaduje sa ďaľšia práca')
    );
  }

  /**
   * Vrat znamku podla jej nazvu, case insensitive
   * @param string $text nazov znamky
   * @returns Znamka znamka, alebo null ak taka znamka neexistuje
   */
  public static function fromString($text)
  {
    Preconditions::checkIsString($text, '$text nazov znamky musi byt string');
    $text = strtoupper($text);
    self::initZnamkyIfNeeded();
    if (!array_key_exists($text, self::$znamky)) {
      return null;
    }
    return self::$znamky[$text];
  }

  /**
   * @returns float numericka hodnota pouzivana napriklad pri pocitani priemeru
   */
  public function getNumerickaHodnota()
  {
    return $this->numerickaHodnota;
  }

  /**
   * @returns string kratky nazov znamky napr. 'A' alebo 'Fx'
   */
  public function getNazov()
  {
    return $this->nazov;
  }

  /**
   * @returns string slovny popis znamky, napr. 'vyborne'
   */
  public function getSlovnyPopis()
  {
    return $this->slovnyPopis;
  }

  /**
   * @returns string dlhy popis znamky, napr. 'vyzaduje sa dalsia praca'
   */
  public function getDlhyPopis()
  {
    return $this->dlhyPopis;
  }

  public function __toString()
  {
    return $this->getNazov();
  }

  /**
   * Skontroluj, ci dve znamky su rovnake
   * @param string $a nazov znamky a
   * @param string $b nazov znamky b
   */
  public static function isSame($a, $b)
  {
    Preconditions::checkIsString($a, 'nazov znamky $a musi byt string');
    Preconditions::checkIsString($b, 'nazov znamky $b musi byt string');
    return self::fromString($a) == self::fromString($b);
  }
  
}
