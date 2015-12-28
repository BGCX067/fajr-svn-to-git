<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

class TableDefinitions
{
  // done
  public static function zoznamStudii()
  {
    return array(
      // {{{
      'rokDoporuceny' => array(
            'title' => 'ročník',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'studijnyProgramSkratka' => array(
            'title' => 'skratka',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'kodKruzok' => array(
            'title' => 'krúžok',
            'sortorder' => '0',
            'visible' => false,
            'col' => 100),
      'studijnyProgramPopis' => array(
            'title' => 'študijný program',
            'sortorder' => '0',
            'visible'=>true,
            'col' => -70),
      'studijnyProgramDoplnUdaje' => array(
            'title' => 'doplňujúce údaje',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'zaciatokStudia' => array(
            'title' => 'začiatok štúdia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -30),
      'koniecStudia' => array(
            'title' => 'koniec štúdia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -20),
      'studijnyProgramDlzka' => array(
            'title' => 'dĺžka v semestroch',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'dobaStudia' => array(
            'title' => 'dĺžka štúdia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -50),
      'cisloDiplomu' => array(
            'title' => 'číslo diplomu',
            'sortorder' => '0',
            'visible' => false),
      'cisloMatriky' => array(
            'title' => 'číslo z matriky',
            'sortorder' => '0',
            'visible' => false),
      'cisloVysvedcenia' => array(
            'title' => 'číslo vysvedčenia',
            'sortorder' => '0',
            'visible' => false),
      'cisloDodatku' => array(
            'title' => 'číslo dodatku',
            'sortorder' => '0',
            'visible' => false),
      'studijnyProgramIdEviCRS' => array(
            'title' => 'číslo EVI',
            'sortorder' => '0',
            'visible' => false),
      'studijnyProgramIdProgramCRS' => array(
            'title' => 'číslo programu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -10),
      'priznak' => array(
            'title' => 'príznak',
            'sortorder' => '0',
            'visible' => false),
      'studijnyProgramSkratkaAkreditOJ' => array(
            'title' => 'organizačná jednotka',
            'sortorder' => '0',
            'visible' => false),
      'rokStudia' => array(
            'title' => 'rok štúdia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      // }}}
    );
  }
  
  // done
  public static function zoznamZapisnychListov()
  {
    return array(
      // {{{
      'popisAkadRok' => array(
            'title' => 'akademický rok',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'rokRocnik' => array(
            'title' => 'ročník',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'studProgramSkratka' => array(
            'title' => 'krúžok',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'studProgramPopis' => array(
            'title' => 'skratka',
            'sortorder' => '0',
            'visible' => true,
            'col' => -70),
      'studProgramDoplnUdaje' => array(
            'title' => 'doplňujúce údaje',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'datumZapisu' => array(
            'title' => 'dátum zápisu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'poplatok' => array(
            'title' => 'potvrdený zápis',
            'sortorder' => '0',
            'visible' => false),
      'podmienecne' => array(
            'title' => 'podmienečný zápis',
            'sortorder' => '0',
            'visible' => false),
      'studProgramDlzka' => array(
            'title' => 'dĺžka v semestroch',
            'sortorder' => '0',
            'visible' => true,
            'col' => -50),
      'studProgramIdEviCRS' => array(
            'title' => 'číslo EVI',
            'sortorder' => '0',
            'visible' => false),
      'studProgramIdProgramCRS' => array(
            'title' => 'číslo programu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -10),
      'datumSplnenia' => array(
            'title' => 'dátum splnenia',
            'sortorder' => '0',
            'visible' => false),
      'priznak' => array(
            'title' => 'príznak',
            'sortorder' => '0',
            'visible' => false),
      'studProgramSkratkaAkreditOJ' => array(
            'title' => 'organizačná jednotka',
            'sortorder' => '0',
            'visible' => false),
      'typFinacovaniaPopis' => array(
            'title' => 'typ financovania',
            'sortorder' => '0',
            'visible' => false),
      'typFinacovaniaSkratPopis' => array(
            'title' => 'skratka typu finacovania',
            'sortorder' => '0',
            'visible' => false),
      // }}}
    );
  }


  // done
  public static function hodnotenia()
  {
    return array(
      // {{{
      'semester' => array(
            'title' => 'Semester',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'kodCastSP' => array(
            'title' => 'Kód časti štúdia',
            'sortorder' => '0',
            'visible' => false),
      'kodTypVyucbySP' => array(
            'title' => 'Kód typu výučby',
            'sortorder' => '0',
            'visible' => false,
            'col' => 0),
      'skratka' => array(
            'title' => 'Skratka predmetu',
            'sortorder' => '0',
            'visible' => false),
      'nazov' => array(
            'title' => 'Názov predmetu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'kredit' => array(
            'title' => 'Kredit predmetu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'kodSposUkon' => array(
            'title' => 'Spôsob ukončenia',
            'sortorder' => '0',
            'visible' => false),
      'termin' => array(
            'title' => 'Termín',
            'sortorder' => '0',
            'visible' => true,
            'col' => -50),
      'znamka' => array(
            'title' => 'Klasifikačný stupeň',
            'sortorder' => '0',
            'visible' => true,
            'col' => -70),
      'datum' => array(
            'title' => 'Dátum hodnotenia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'uznane' => array(
            'title' => 'Uznané hodnotenie',
            'sortorder' => '0',
            'visible' => false),
      'blokPopis' => array(
            'title' => 'Popis bloku',
            'sortorder' => '0',
            'visible' => false),
      'poplatok' => array(
            'title' => 'Zaplatený poplatok a úplný zápis',
            'sortorder' => '0',
            'visible' => false),
      'nahradzaMa' => array(
            'title' => 'Nahrádza ma',
            'sortorder' => '0',
            'visible' => false),
      'nahradzam' => array(
            'title' => 'Nahrádzam',
            'sortorder' => '0',
            'visible' => false),
      'znamkaPopis' => array(
            'title' => 'Známka popis',
            'sortorder' => '0',
            'visible' => true), // TODO(ppershing): check me out
      'dovezene' => array(
            'title' => 'Dovezené hodnotenie',
            'sortorder' => '0',
            'visible' => false),
      'mozePrihlasit' => array(
            'title' => 'Môže prihlásiť',
            'sortorder' => '0',
            'visible' => false),
      'rozsah' => array(
            'title' => 'Rozsah',
            'sortorder' => '0',
            'visible' => false),
      'priebHodn' => array(
            'title' => 'Existuje priebežné hodnotenie',
            'sortorder' => '0',
            'visible' => false),
      // }}}
    );
  }
  
  public static function priemery()
  {
    return array(
      // {{{
      'priemerInfoPopisAkadRok' => array(
            'title' => 'Akademický rok',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'priemerInfoKodSemester' => array(
            'title' => 'Kód semestra',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'vazPriemer' => array(
            'title' => 'Vážený priemer',
            'sortorder' => '0',
            'visible' => true,
            'col' => -5),
      'studPriemer' => array(
            'title' => 'Štúdijný priemer',
            'sortorder' => '0',
            'visible' => true,
            'col' => -10),
      'pocetPredmetov' => array(
            'title' => 'Celkový počet predmetov',
            'sortorder' => '0',
            'visible' => true,
            'col' => -30),
      'pocetNeabs' => array(
            'title' => 'Počet neabsolvovaných predmetov',
            'sortorder' => '0',
            'visible' => true,
            'col' => -20),
      'pokusyPriemer' => array(
            'title' => 'Priemer na koľký pokus',
            'sortorder' => '0',
            'visible' => true,
            'col' => -1),
      'ziskanyKredit' => array(
            'title' => 'Získaný kredit',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'prerusUkon' => array(
            'title' => 'Študujúci, prerušený, ukončený',
            'sortorder' => '0',
            'visible' => false),
      'priemerInfoDatum' => array(
            'title' => 'Dátum výpočtu priemeru',
            'sortorder' => '0',
            'visible' => true),
      'priemerInfoDatum1Hodn' => array(
            'title' => 'Dátum jednofázového hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'priemerInfoDatum2Hodn' => array(
            'title' => 'Dátum dvojfázového hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'priemerNazov' => array(
            'title' => 'Názov priemeru',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'priemerZaAkRok' => array(
            'title' => 'Priemer je počítaný za akademický rok',
            'sortorder' => '0',
            'visible' => false),
      'priemerZaSemester' => array(
            'title' => 'Priemer je počítaný za semester',
            'sortorder' => '0',
            'visible' => false),
      'priemerLenStudPlan' => array(
            'title' => 'Započítane len predmety študijného plánu',
            'sortorder' => '0',
            'visible' => false),
      'priemerUznanePredm' => array(
            'title' => 'Počítať uznané predmety',
            'sortorder' => '0',
            'visible' => false),
      'priemerAjDatum1Hodn' => array(
            'title' => 'Brať do úvahy dátum jednofázového hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'priemerAjDatum2Hodn' => array(
            'title' => 'Brať do úvahy dátum dvojfázového hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'priemerPocitatNeabs' => array(
            'title' => 'Počítať predmety bez hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'priemerVahaNeabsolvovanych' => array(
            'title' => 'Váha neabsolvovaných predmetov',
            'sortorder' => '0',
            'visible' => false),
      'priemerSkratkaOrganizacnaJednotka' => array(
            'title' => 'Skratka organizačnej jednotky',
            'sortorder' => '0',
            'visible' => false),
      'priemerPocitatNeabsC' => array(
            'title' => 'Nepočítať výberové bez ukončeného hodnotenia',
            'sortorder' => '0',
            'visible' => false),
      'pocetPredmetovVyp' => array(
            'title' => 'Počet predmetov výpočtu',
            'sortorder' => '0',
            'visible' => false),
      'priemerInfoStudentiVypoctu' => array(
            'title' => 'Určujúca množina študentov výpočtu priemeru',
            'sortorder' => '0',
            'visible' => false),
      // }}}
    );
  }
 
  // done
  public static function predmetyZapisnehoListu()
  {
    return array(
      // {{{
      'kodCastStPlanu' => array(
            'title' => 'kód časti študijného plánu',
            'sortorder' => '0',
            'visible' => false),
      'kodTypVyucby' => array(
            'title' => 'kód typu výučby',
            'sortorder' => '0',
            'visible' => true,
            'col' => -70),
      'skratka' => array(
            'title' => 'skratka',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'nazov' => array(
            'title' => 'názov predmetu',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'kredit' => array(
            'title' => 'kredit',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'kodSemester' => array(
            'title' => 'semester',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'kodSposUkon' => array(
            'title' => 'spôsob ukončenia',
            'sortorder' => '0',
            'visible' => false),
      'pocetTerminov' => array(
            'title' => 'počet termínov',
            'sortorder' => '0',
            'visible' => false),
      'pocetAktualnychTerminov' => array(
            'title' => 'počet aktuálnych termínov',
            'sortorder' => '0',
            'visible' => false),
      'aktualnost' => array(
            'title' => 'aktuálnosť',
            'sortorder' => '0',
            'visible' => false),
      // }}}
    );
  }
  
  public static function mojeTerminyHodnotenia()
  {
    return array(
      // {{{
      // specialne
      'odhlas' => array(
            'title' => 'Odhlás',
            'sortorder' => '0',
            'visible' => true),
      // originalne
      'jePrihlaseny' => array(
            'title' => 'prihlásený',
            'sortorder' => '0',
            'visible' => false,
            'col' => 0),
      'kodFaza' => array(
            'title' => 'fáza',
            'sortorder' => '0',
            'visible' => false),
      'dat' => array(
            'title' => 'dátum',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'cas' => array(
            'title' => 'čas',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'miestnosti' => array(
            'title' => 'miestnosť',
            'sortorder' => '0',
            'visible' => true,
            'col' => -70),
      'pocetPrihlasenych' => array(
            'title' => 'počet prihlásených',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'datumPrihlas' => array(
            'title' => 'dátum prihlásenia',
            'sortorder' => '0',
            'visible' => false),
      'datumOdhlas' => array(
            'title' => 'dátum odhlásenia',
            'sortorder' => '0',
            'visible' => false),
      'zapisal' => array(
            'title' => 'zapísal',
            'sortorder' => '0',
            'visible' => false),
      'pocetHodn' => array(
            'title' => 'počet hodnotiacich',
            'sortorder' => '0',
            'visible' => false),
      'hodnotiaci' => array(
            'title' => 'hodnotiaci',
            'sortorder' => '0',
            'visible' => true,
            'col' => -65),
      'maxPocet' => array(
            'title' => 'maximálny počet',
            'sortorder' => '0',
            'visible' => true,
            'col' => -50),
      'znamka' => array(
            'title' => 'známka',
            'sortorder' => '0',
            'visible' => true),
      'prihlasovanie' => array(
            'title' => 'prihlasovanie',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'odhlasovanie' => array(
            'title' => 'odhlasovanie',
            'sortorder' => '0',
            'visible' => true,
            'col' => -30),
      'poznamka' => array(
            'title' => 'poznámka',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'zaevidoval' => array(
            'title' => 'zaevidoval',
            'sortorder' => '0',
            'visible' => false),
      'mozeOdhlasit' => array(
            'title' => 'može odhlásiť',
            'sortorder' => '0',
            'visible' => false),
      'predmetSkratka' => array(
            'title' => 'skratka predmetu',
            'sortorder' => '0',
            'visible' => false),
      'predmetNazov' => array(
            'title' => 'predmet',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      // }}}
    );
  }
  
  public static function vyberTerminuHodnoteniaJoined()
  {
    return array(
      // {{{
      // udaje vzniknute pocas joinovania
      'predmet' => array(
            'title' => 'Predmet',
            'sortorder' => '0',
            'visible' => true,
            'col' => -100),
      'predmetIndex' => array(
            'title' => 'PredmetIndex',
            'sortorder' => '0',
            'visible' => false),
      'prihlas' => array(
            'title' => 'Prihlás',
            'sortorder' => '0',
            'visible' => true),
      // originalne udaje
      'kodFaza' => array(
            'title' => 'Kód fázy',
            'sortorder' => '0',
            'visible' => false),
      'dat' => array(
            'title' => 'Dátum',
            'sortorder' => '0',
            'visible' => true,
            'col' => -80),
      'cas' => array(
            'title' => 'Čas',
            'sortorder' => '0',
            'visible' => true,
            'col' => -90),
      'miestnosti' => array(
            'title' => 'Miestnosti',
            'sortorder' => '0',
            'visible' => true,
            'col' => -70),
      'pocetPrihlasenych' => array(
            'title' => 'Počet prihlásených študentov',
            'sortorder' => '0',
            'visible' => true,
            'col' => -40),
      'maxPocet' => array(
            'title' => 'Maximálny počet',
            'sortorder' => '0',
            'visible' => true,
            'col' => -30),
      'pocetHodn' => array(
            'title' => 'Počet hodnotiacich',
            'sortorder' => '0',
            'visible' => false),
      'hodnotiaci' => array(
            'title' => 'Hodnotiaci',
            'sortorder' => '0',
            'visible' => true,
            'col' => -60),
      'prihlasovanie' => array(
            'title' => 'Interval pre prihlasovanie',
            'sortorder' => '0',
            'visible' => true,
            'col' => -20),
      'odhlasovanie' => array(
            'title' => 'Interval pre odhlasovanie',
            'sortorder' => '0',
            'visible' => true,
            'col' => -10),
      'poznamka' => array(
            'title' => 'Poznámka',
            'sortorder' => '0',
            'visible' => true,
            'col' => -30),
      'zaevidoval' => array(
            'title' => 'Zaevidoval',
            'sortorder' => '0',
            'visible' => false),
      // }}}
    );
  }
  

  // done
  public static function zoznamPrihlasenych()
  {
    return array(
      // {{{
      'meno' => array(
            'title' => 'Meno',
            'sortorder' => '0',
            'visible' => false,
            'col' => 0),
      'priezvisko' => array(
            'title' => 'Priezvisko',
            'sortorder' => '0',
            'visible' => false,
            'col' => 10),
      'skratka' => array(
            'title' => 'Skratka študijného programu',
            'sortorder' => '0',
            'visible' => true,
            'col' => 20),
      'datumPrihlas' => array(
            'title' => 'Dátum prihlásenia',
            'sortorder' => '0',
            'visible' => true,
            'col' => -10),
      'plneMeno' => array(
            'title' => 'Plné meno',
            'sortorder' => '0',
            'visible' => true,
            'col' => -5),
      'rocnik' => array(
            'title' => 'Ročník',
            'sortorder' => '0',
            'visible' => true,
            'col' => 30),
      'kruzok' => array(
            'title' => 'Krúžok',
            'sortorder' => '0',
            'visible' => false,
            'col' => 40),
      // }}}
    );
  }
}
