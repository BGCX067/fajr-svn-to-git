<?php
namespace fajr\controller\studium;

/**
 * This class is here just to enable including of rest of this file
 * without need of explicit path inclusion.
 * usage: fields::autoload()
 */
class fields
{
  public static function autoload()
  {
  }
}

/* getTerminyHodnotenia()
array (
  0 => 'jePrihlaseny',
  1 => 'kodFaza',
  2 => 'dat',
  3 => 'cas',
  4 => 'miestnosti',
  5 => 'pocetPrihlasenych',
  6 => 'datumPrihlas',
  7 => 'datumOdhlas',
  8 => 'zapisal',
  9 => 'pocetHodn',
  10 => 'hodnotiaci',
  11 => 'maxPocet',
  12 => 'znamka',
  13 => 'prihlasovanie',
  14 => 'odhlasovanie',
  15 => 'poznamka',
  16 => 'zaevidoval',
  17 => 'mozeOdhlasit',
  18 => 'predmetSkratka',
  19 => 'predmetNazov',
  20 => 'moznostPrihlasenia',
)
*/
class TerminyFields
{
  const INDEX = 'index';
  const DATUM = 'dat';
  const CAS = 'cas';
  const JE_PRIHLASENY = 'jePrihlaseny';
  const MOZE_ODHLASIT = 'mozeOdhlasit';
  const ZNAMKA = 'znamka';
  const PREDMET_SKRATKA = 'predmetSkratka';
}
class MojeTerminyFields extends TerminyFields {
  const HASH_ODHLASENIE = 'hashNaOdhlasenie';
}

/* getZoznamTerminovDialog($predmet)->getZoznamTerminov();
array(14) { 
["index"]=> int(0) 
["kodFaza"]=> string(1) "1" 
["dat"]=> string(10) "05.01.2011" 
["cas"]=> string(5) "09:00" 
["miestnosti"]=> string(10) "FMFI M XII" 
["pocetPrihlasenych"]=> string(1) "2" 
["maxPocet"]=> string(2) "30" 
["pocetHodn"]=> string(1) "1" 
["hodnotiaci"]=> string(29) "doc. RNDr. Eduard Toman, CSc." 
["prihlasovanie"]=> string(19) "do 04.01.2011 09:00" 
["odhlasovanie"]=> string(19) "do 04.01.2011 09:00" 
["poznamka"]=> string(0) "" 
["zaevidoval"]=> string(29) "doc. RNDr. Eduard Toman, CSc." 
["moznostPrihlasenia"]=> string(1) "A"
}
*/
class ZoznamTerminovFields {
  const MAX_POCET = 'maxPocet';
  const POCET_PRIHLASENYCH = 'pocetPrihlasenych';
  const PRIHLASOVANIE_DATUM = 'prihlasovanie';
  const DATUM = 'dat';
  const CAS = 'cas';
  const INDEX = 'index';
}

class PrihlasTerminyFields extends ZoznamTerminovFields
{
  const HASH_PRIHLASENIE = 'hashNaPrihlasenie';
  const PREDMET_INDEX = 'predmetIndex';
  const PREDMET_SKRATKA = 'predmetSkratka';
  const PREDMET = 'predmet';
  const FAJR_MOZE_PRIHLASIT = 'mozeSaPrihlasit';
  const ZNAMKA = 'znamka';
}
/* getHodnotenia
array (
  0 => 'semester',
  1 => 'kodCastSP',
  2 => 'kodTypVyucbySP',
  3 => 'skratka',
  4 => 'nazov',
  5 => 'kredit',
  6 => 'kodSposUkon',
  7 => 'termin',
  8 => 'znamka',
  9 => 'datum',
  10 => 'uznane',
  11 => 'blokPopis',
  12 => 'poplatok',
  13 => 'nahradzaMa',
  14 => 'nahradzam',
  15 => 'znamkaPopis',
  16 => 'dovezene',
  17 => 'mozePrihlasit',
  18 => 'rozsah',
  19 => 'priebHodn',
)
*/
class HodnoteniaFields
{
  const MOZE_PRIHLASIT = 'mozePrihlasit';
  const PREDMET_SKRATKA = 'skratka';
  const ZNAMKA = 'znamka';
  const KREDIT = 'kredit';
  const SEMESTER = 'semester';
}

/* getPredmetyZapisnehoListu()
array (
  0 => 'kodCastStPlanu',
  1 => 'kodTypVyucby',
  2 => 'skratka',
  3 => 'nazov',
  4 => 'kredit',
  5 => 'kodSemester',
  6 => 'kodSposUkon',
  7 => 'pocetTerminov',
  8 => 'pocetAktualnychTerminov',
  9 => 'aktualnost',
)
*/
class PredmetyFields
{
  const INDEX = 'index';
  const NAZOV = 'nazov';
  const SKRATKA = 'skratka';
  const KREDIT = 'kredit';
  const SEMESTER = 'kodSemester';
}
