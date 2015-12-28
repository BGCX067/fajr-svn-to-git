<?php

/**
 * This file contains tests for InformacnyListDataImpl
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\data_manipulation;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\data_manipulation\InformacnyListDataImpl;
use fajr\libfajr\data_manipulation\InformacnyListAttributeEnum;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class InformacnyListDataImplTest extends PHPUnit_Framework_TestCase {

    public $informacnyList;

    public function setUp() {

        $list = array(
            InformacnyListAttributeEnum::NAZOV => "Kryptologia (2)",
            InformacnyListAttributeEnum::STUDIJNY_PROGRAM => "mINF, mINF/k",
            InformacnyListAttributeEnum::POCET_KREDITOV => "6"
            );
        $this->informacnyList = new InformacnyListDataImpl($list);

    }

    public function testExistenciaAtributov() {
        $this->assertTrue($this->informacnyList->hasAttribute(InformacnyListAttributeEnum::NAZOV));
        $this->assertTrue($this->informacnyList->hasAttribute(InformacnyListAttributeEnum::STUDIJNY_PROGRAM));
        $this->assertFalse($this->informacnyList->hasAttribute('b34gh'));
    }

    public function testExistenciaAtributov2() {
        foreach ($this->informacnyList->getListOfAttributes() as $attribute) {
            $this->assertTrue($this->informacnyList->hasAttribute($attribute));
        }
    }

    public function testHodnotyAtributov() {
        $this->assertEquals('mINF, mINF/k', $this->informacnyList->getAttribute(InformacnyListAttributeEnum::STUDIJNY_PROGRAM));
        $this->assertEquals('6', $this->informacnyList->getAttribute(InformacnyListAttributeEnum::POCET_KREDITOV));
        $this->assertFalse($this->informacnyList->getAttribute('b34gh'));
    }

    public function testKonzistenciaGetAllAttributes() {
        $attributes = $this->informacnyList->getAllAttributes();
        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, $this->informacnyList->getAttribute($key));
        }
    }

}
