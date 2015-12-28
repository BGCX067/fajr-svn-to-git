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
namespace fajr;
use fajr\htmlgen\Renderable;

class PriemeryInternal
{
  protected $sucet = 0;
  protected $sucetVah = 0;
  protected $pocet = 0; // TODO(ppershing): rename to pocetPredmetov
  protected $pocetKreditov = 0;
  protected $pocetNeohodnotenych = 0; // TODO(ppershing): rename
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
    $this->pocet += 1;
    $this->pocetKreditov += $kredity;
  }

  private function addNeohodnotene($kredity)
  {
    $this->pocetNeohodnotenych += 1;
    $this->pocetKreditovNeohodnotenych += $kredity;
  }

  public function add($znamka, $kredity)
  {
    if (isset(PriemeryInternal::$numerickaHodnotaZnamky[$znamka])) {
      $hodnota = PriemeryInternal::$numerickaHodnotaZnamky[$znamka];
      $this->addOhodnotene($hodnota, $kredity);
    }
    else { // FIXME(co tak porovnat na '' a pripadne vyrazit exception ak
           // je to neocakavana znamka?
      $this->addNeohodnotene($kredity);
    }
  }

  public function studijnyPriemer($neohodnotene = true)
  {
    $suma = $this->sucet;
    $pocet = $this->pocet;

    if ($neohodnotene) {
      $suma += $this->pocetNeohodnotenych*self::$numerickaHodnotaZnamky['Fx'];
      $pocet += $this->pocetNeohodnotenych;
    }

    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  public function vazenyPriemer($neohodnotene=true)
  {
    $suma = $this->sucetVah;
    $pocet = $this->pocetKreditov;
    if ($neohodnotene) {
      $suma += $this->pocetKreditovNeohodnotenych*self::$numerickaHodnotaZnamky['Fx'];
      $pocet += $this->pocetKreditovNeohodnotenych;
    }
    if ($pocet == 0) {
      return null;
    }
    return $suma/$pocet;
  }

  public function hasPriemer()
  {
    return $this->pocet > 0;
  }

}

class PriemeryCalculator implements Renderable
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

  private function vypisVazenyPriemer($castRoka)
  {
    $sNeohodnotenymi = $this->obdobia[$castRoka]->vazenyPriemer(true);
    $ibaOhodnotene = $this->obdobia[$castRoka]->vazenyPriemer(false);
    $text = sprintf('%.2f', $sNeohodnotenymi);
    if ($sNeohodnotenymi !== $ibaOhodnotene) {
      $text .= ' ('.sprintf('%.2f', $ibaOhodnotene).' iba doteraz ohodnotené predmety)';
    }
    return $text;
  }

  public function getHtml()
  {
    $html = '';
    if ($this->obdobia[self::SEMESTER_ZIMNY]->hasPriemer()) {
      $html .= 'Zimný semester: '.$this->vypisVazenyPriemer(self::SEMESTER_ZIMNY).'<br />';
    }

    if ($this->obdobia[self::SEMESTER_LETNY]->hasPriemer()) {
      $html .= 'Letný semester: '.$this->vypisVazenyPriemer(self::SEMESTER_LETNY).'<br />';
    }

    if ($this->obdobia[self::AKADEMICKY_ROK]->hasPriemer()) {
      $html .= 'Celý akad. rok: '.$this->vypisVazenyPriemer(self::AKADEMICKY_ROK).'<br />';
    }
    return $html;
  }

}
