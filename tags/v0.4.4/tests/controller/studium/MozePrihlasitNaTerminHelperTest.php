<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for FajrUtils class
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';
fields::autoload();

/**
 * @ignore
 */
class MozePrihlasitNaTerminHelperTest extends PHPUnit_Framework_TestCase
{
  private $helper;
  public function setUp()
  {
    $hodnotenia = array (
        '1-INF-47' => array (
            HodnoteniaFields::ZNAMKA => '',
            HodnoteniaFields::MOZE_PRIHLASIT => 'A',
          ),
        '1-INF-47x' => array (
            HodnoteniaFields::ZNAMKA => '',
            HodnoteniaFields::MOZE_PRIHLASIT => 'N',
          ),
        '1-INF-48' => array (
            HodnoteniaFields::ZNAMKA => 'A',
            HodnoteniaFields::MOZE_PRIHLASIT => 'A',
          ),
        '1-INF-48x' => array (
            HodnoteniaFields::ZNAMKA => 'A',
            HodnoteniaFields::MOZE_PRIHLASIT => 'N',
          ),
        );
    $this->helper = new MozePrihlasitNaTerminHelper($hodnotenia);
  }

  public function testNemozeDatum()
  {
    $row = array (
        PrihlasTerminyFields::PRIHLASOVANIE_DATUM => '01.01.2010 00:00 do 01.01.2011 00:00',
        PrihlasTerminyFields::PREDMET_SKRATKA => '1-INF-47',
        PrihlasTerminyFields::MAX_POCET => '',
        PrihlasTerminyFields::POCET_PRIHLASENYCH => '',
        );
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_MOZE,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));

    $row = array (
        PrihlasTerminyFields::PRIHLASOVANIE_DATUM => '01.01.2010 00:00 do 01.01.2011 00:00',
        PrihlasTerminyFields::PREDMET_SKRATKA => '1-INF-47',
        PrihlasTerminyFields::MAX_POCET => '',
        PrihlasTerminyFields::POCET_PRIHLASENYCH => '',
        );
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_MOZE,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_NEMOZE_CAS,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2009-02-02")));
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_NEMOZE_CAS,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2011-02-02")));
  }

  public function testNemozeZnamka()
  {
    $row = array (
        PrihlasTerminyFields::PRIHLASOVANIE_DATUM => '01.01.2011 08:00',
        PrihlasTerminyFields::PREDMET_SKRATKA => '1-INF-48x',
        PrihlasTerminyFields::MAX_POCET => '',
        PrihlasTerminyFields::POCET_PRIHLASENYCH => '',
        );
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_NEMOZE_ZNAMKA,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
  }

  public function testMozeZnamka()
  {
    $row = array (
        PrihlasTerminyFields::PRIHLASOVANIE_DATUM => 'do 01.01.2011 08:00',
        PrihlasTerminyFields::PREDMET_SKRATKA => '1-INF-48',
        PrihlasTerminyFields::MAX_POCET => '',
        PrihlasTerminyFields::POCET_PRIHLASENYCH => '',
        );
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_MOZE_ZNAMKA,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
  }

  public function testNemozePocet()
  {
    $row = array (
        PrihlasTerminyFields::PRIHLASOVANIE_DATUM => 'do 01.01.2011 08:00',
        PrihlasTerminyFields::PREDMET_SKRATKA => '1-INF-47',
        PrihlasTerminyFields::MAX_POCET => '4',
        PrihlasTerminyFields::POCET_PRIHLASENYCH => '',
        );
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_MOZE,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
    $row[PrihlasTerminyFields::POCET_PRIHLASENYCH] = '3';
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_MOZE,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));

    $row[PrihlasTerminyFields::POCET_PRIHLASENYCH] = '4';
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_NEMOZE_POCET,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
    $row[PrihlasTerminyFields::POCET_PRIHLASENYCH] = '5';
    $this->assertEquals(MozePrihlasitNaTerminHelper::PRIHLASIT_NEMOZE_POCET,
                        $this->helper->mozeSaPrihlasit($row, strtotime("2010-02-02")));
  }
}
