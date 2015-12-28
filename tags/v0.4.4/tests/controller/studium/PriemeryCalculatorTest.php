<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for PriemeryCalculator class
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

/**
 * @ignore
 */
class PriemeryCalculatorTest extends PHPUnit_Framework_TestCase
{
  private $calculator;
  public function setUp()
  {
    $this->calculator = new PriemeryCalculator();
  }

  public function testEmpty()
  {
    $this->assertEquals(false, $this->calculator->hasPriemer());
    $obdobia = $this->calculator->getObdobia();
    $this->assertEquals(false, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->hasPriemer());
    $this->assertEquals(0, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->kreditovCelkom());
    $this->assertEquals(0, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->predmetovCelkom());
    $this->assertEquals(null, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->vazenyPriemer());
    $this->assertEquals(null, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->studijnyPriemer());
  }


  public function testValidParameters()
  {
    $this->calculator->add(PriemeryCalculator::SEMESTER_LETNY, 'A', 1);
  }

  public function testInvalidSemester()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->calculator->add('junk', 'A', 1);
  }

  public function testInvalidZnamka()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->calculator->add(PriemeryCalculator::SEMESTER_LETNY, 'Invalid', 1);
  }

  public function testInvalidKredit()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->calculator->add(PriemeryCalculator::SEMESTER_LETNY, 'A', 'xx');
  }

  public function testInvalidKredit2()
  {
    $this->setExpectedException('InvalidArgumentException');
    $this->calculator->add(PriemeryCalculator::SEMESTER_LETNY, 'A', -1);
  }

  private function addDefaultTestData(PriemeryCalculator $calculator)
  {
    $data = array(
        array(PriemeryCalculator::SEMESTER_LETNY, 'A', 1),
        array(PriemeryCalculator::SEMESTER_LETNY, 'B', 2),
        array(PriemeryCalculator::SEMESTER_LETNY, '', 2),
        array(PriemeryCalculator::SEMESTER_ZIMNY, 'C', 8),
        array(PriemeryCalculator::SEMESTER_ZIMNY, '', 2)
        );
    foreach ($data as $row) {
      $calculator->add($row[0], $row[1], $row[2]);
    }
  }

  public function testPocetPredmetov()
  {
    $this->addDefaultTestData($this->calculator);
    $obdobia = $this->calculator->getObdobia();
    $this->assertEquals(3, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->predmetovCelkom());
    $this->assertEquals(2, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->predmetovCelkom());
    $this->assertEquals(5, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->predmetovCelkom());
  }

  public function testPocetKreditov()
  {
    $this->addDefaultTestData($this->calculator);
    $obdobia = $this->calculator->getObdobia();
    $this->assertEquals(5, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->kreditovCelkom());
    $this->assertEquals(10, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->kreditovCelkom());
    $this->assertEquals(15, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->kreditovCelkom());
  }

  public function testStudijnyPriemer()
  {
    $this->addDefaultTestData($this->calculator);
    $obdobia = $this->calculator->getObdobia();

    $this->assertEquals(1.25, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->studijnyPriemer(false));
    $this->assertEquals(6.5/3, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->studijnyPriemer(true));
    $this->assertEquals(2.0, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->studijnyPriemer(false));
    $this->assertEquals(3.0, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->studijnyPriemer(true));
    $this->assertEquals(1.5, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->studijnyPriemer(false));
    $this->assertEquals(2.5, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->studijnyPriemer(true));
  }

  public function testVazenyPriemer()
  {
    $this->addDefaultTestData($this->calculator);
    $obdobia = $this->calculator->getObdobia();

    $this->assertEquals(4.0/3.0, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->vazenyPriemer(false));
    $this->assertEquals(12.0/5.0, $obdobia[PriemeryCalculator::SEMESTER_LETNY]->vazenyPriemer(true));
    $this->assertEquals(2.0, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->vazenyPriemer(false));
    $this->assertEquals(2.4, $obdobia[PriemeryCalculator::SEMESTER_ZIMNY]->vazenyPriemer(true));
    $this->assertEquals(20.0/11.0, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->vazenyPriemer(false));
    $this->assertEquals(36.0/15.0, $obdobia[PriemeryCalculator::AKADEMICKY_ROK]->vazenyPriemer(true));
  }
}
