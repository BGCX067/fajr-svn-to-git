<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Znamka
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\data_manipulation;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\data_manipulation\Znamka;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class ZnamkaTest extends PHPUnit_Framework_TestCase
{
  

  public function testZiskavanieZnamok()
  {
    $a = Znamka::fromString('A');
    $this->assertEquals(1.0, $a->getNumerickaHodnota());
    $b = Znamka::fromString('b');
    $this->assertEquals(1.5, $b->getNumerickaHodnota());
    $fx = Znamka::fromString('Fx');
    $this->assertEquals(4, $fx->getNumerickaHodnota());
    $nic = Znamka::fromString('nieco divne');
    $this->assertEquals(null, $nic);
  }

  public function testPorovnavaniaZnamok()
  {
    $this->assertTrue(Znamka::isSame('a', 'a'));
    $this->assertTrue(Znamka::isSame('a', 'A'));
    $this->assertTrue(Znamka::isSame('A', 'a'));
    $this->assertTrue(Znamka::isSame('Fx', 'Fx'));
    $this->assertTrue(Znamka::isSame('FX', 'Fx'));
    $this->assertTrue(Znamka::isSame('Fx', 'FX'));
    $this->assertTrue(Znamka::isSame('fX', 'fx'));
    $this->assertTrue(Znamka::isSame('fX', 'FX'));
    $this->assertFalse(Znamka::isSame('a', 'B'));
    $this->assertFalse(Znamka::isSame('Fx', 'Fxx'));
    $this->assertFalse(Znamka::isSame('C', 'EC'));
  }

}
