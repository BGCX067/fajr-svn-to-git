<?php
/**
 * This file contains tests for PearsonChiSquare statistics.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Statistics
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use fajr\lib\statistics\PearsonChiSquare;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class PearsonChiSquareTest extends PHPUnit_Framework_TestCase
{
  public function testWrongArgumentsMismatchCount()
  {
    $this->setExpectedException('\InvalidArgumentException');
    PearsonChiSquare::chiSquare(array(100, 200), array(0.3, 0.3, 0.4));
  }

  public function testWrongArgumentsNegativeProb()
  {
    $this->setExpectedException('\InvalidArgumentException');
    PearsonChiSquare::chiSquare(array(100, 200), array(-0.3, 1.3));
  }

  // probabilities does not sum into one
  public function testWrongArgumentsSumNotOne()
  {
    $this->setExpectedException('\InvalidArgumentException');
    PearsonChiSquare::chiSquare(array(100, 200), array(0.3, 0.3));
  }

  public function testChiSquareSimple()
  {
    $this->assertEquals(1.44, PearsonChiSquare::chiSquare(array(44, 56), array(0.5, 0.5)),
        '', 1e-5);

    $this->assertEquals(4.54, PearsonChiSquare::chiSquare(array(14, 21, 25),
          array(0.167, 0.483, 0.350)),
        '', 0.01);

    $this->assertEquals(135.93, PearsonChiSquare::chiSquare(array(700, 790, 30, 40),
          array(0.54, 0.40, 0.05, 0.01)),
        '', 0.01);

    $this->assertEquals(0.35, PearsonChiSquare::chiSquare(array(423, 133), array(0.75, 0.25)),
        '', 0.1);
  }

  public function testPvalueWikiValues()
  {
    // values from
    // http://en.wikipedia.org/wiki/Chi-square_distribution#Table_of_.CF.87.C2.B2_value_vs_P_value
    $this->assertEquals(0.95, PearsonChiSquare::pvalue(1, 0.004), '', 0.005);
    $this->assertEquals(0.95, PearsonChiSquare::pvalue(3, 0.35), '', 0.005);
    $this->assertEquals(0.95, PearsonChiSquare::pvalue(6, 1.63), '', 0.005);

    $this->assertEquals(0.50, PearsonChiSquare::pvalue(1, 0.46), '', 0.005);
    $this->assertEquals(0.50, PearsonChiSquare::pvalue(3, 2.37), '', 0.005);
    $this->assertEquals(0.50, PearsonChiSquare::pvalue(6, 5.35), '', 0.005);

    $this->assertEquals(0.05, PearsonChiSquare::pvalue(1, 3.84), '', 0.005);
    $this->assertEquals(0.05, PearsonChiSquare::pvalue(3, 7.82), '', 0.005);
    $this->assertEquals(0.05, PearsonChiSquare::pvalue(6, 12.59), '', 0.005);
  }

  public function testPvalueHighPrecision()
  {
    $this->assertEquals(0.9906109591, PearsonChiSquare::pvalue(3, 0.11), '', 1e-9);
    $this->assertEquals(0.9598403687, PearsonChiSquare::pvalue(7, 2), '', 1e-9);
    $this->assertEquals(0.005569683073, PearsonChiSquare::pvalue(7, 20), '', 1e-9);
  }

}
