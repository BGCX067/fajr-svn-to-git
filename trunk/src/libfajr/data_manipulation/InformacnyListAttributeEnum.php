<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use InvalidArgumentException;

/**
 * Class defining possible items on information sheet.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class InformacnyListAttributeEnum
{
  const SKOLA_FAKULTA = 'nazov_vysokej_skoly_a_fakulty';
  const KOD = 'kod';
  const NAZOV = 'nazov';
  const STUDIJNY_PROGRAM = 'studijny_program';
  const GARANTUJE = 'garantuje';
  const ZABEZPECUJE = 'zabezpecuje';
  const OBDOBIE_STUDIA_PREDMETU = 'obdobie_studia_predmetu';
  const FORMA_VYUCBY = 'forma_vyucby';
  const VYUCBA_TYZDENNE = 'odporucany_rozsah_vyucby_tyzdenne_v_hodinách';
  const VYUCBA_SPOLU = 'odporucany_rozsah_vyucby_spolu_v_hodinách';
  const POCET_KREDITOV = 'pocet_kreditov';
  const PODMIENUJUCE_PREDMETY = 'podmienujuce_predmety';
  const OBSAHOVA_PREREKVIZITA = 'obsahova_prerekvizita';
  const SPOSOB_HODNOTENIA_A_SKONCENIA = 'sposob_hodnotenia_a_skoncenia_studia_predmetu';
  const PRIEBEZNE_HODNOTENIE = 'priebezne_hodnotenie';
  const ZAVERECNE_HODNOTENIE = 'zaverecne_hodnotenie';
  const CIEL_PREDMETU = 'ciel_predmetu';
  const OSNOVA_PREDMETU = 'strucna_osnova_predmetu';
  const LITERATURA = 'literatura';
  const VYUCOVACI_JAZYK = 'jazyk_v_ktorom_sa_predmet_vyucuje';
  const DATUM_POSLEDNEJ_UPRAVY = 'datum_poslednej_upravy_listu';

  /**
   * Returns slovak description for chosen attribute.
   *
   * @param string $name Attribute name.
   * @returns string Attribute description in utf8.
   * @throws InvalidArgumentException If chosen attribute does not exists.
   */
  public static function getUnicodeName($name)
  {
    $attributeDescriptions = array(
      self::SKOLA => 'Názov vysokej školy, názov fakulty',
      self::KOD => 'Kód',
      self::NAZOV => 'Názov',
      self::STUDIJNY => 'Študijný program',
      self::GARANTUJE => 'Garantuje',
      self::ZABEZPECUJE => 'Zabezpečuje',
      self::OBDOBIE => 'Obdobie štúdia predmetu',
      self::FORMA => 'Forma výučby',
      self::VYUCBA => 'Odporúčaný rozsah výučby týždenne (v hodinách)',
      self::VYUCBA => 'Odporúčaný rozsah výučby spolu (v hodinách)',
      self::POCET => 'Počet kreditov',
      self::PODMIENUJUCE => 'Podmieňujúce predmety',
      self::OBSAHOVA => 'Obsahová prerekvizita',
      self::SPOSOB => 'Spôsob hodnotenia a skončenia štúdia predmetu',
      self::PRIEBEZNE => 'Priebežné hodnotenie',
      self::ZAVERECNE => 'Záverečné hodnotenie',
      self::CIEL => 'Cieľ predmetu',
      self::OSNOVA => 'Stručná osnova predmetu',
      self::LITERATURA  => 'Literatúra',
      self::VYUCOVACI => 'Jazyk, v ktorom sa predmet vyučuje',
      self::DATUM => 'Dátum poslednej úpravy listu',
    );

    if (array_key_exists($name, $attributeDescriptions)) return $attributeDescriptions[$name];
    else throw new InvalidArgumentException('Attribute "'.$name.'" does not exists.');
  }
  
}
