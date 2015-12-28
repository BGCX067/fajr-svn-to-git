<?php
/**
 * This file contains original tests for ContinuedFraction clas
 *
 * @copyright  Copyright 2003-2004 The Apache Software Foundation.
 *             Licensed under the Apache License, Version 2.0 (the "License");
 *             you may not use this file except in compliance with the License.
 *             You may obtain a copy of the License at
 *             http://www.apache.org/licenses/LICENSE-2.0
 *             Unless required by applicable law or agreed to in writing, software
 *             distributed under the License is distributed on an "AS IS" BASIS,
 *             WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *             See the License for the specific language governing permissions and
 *             limitations under the License.
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Math
 * @author     org.apache.commons
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\lib\math;
use PHPUnit_Framework_TestCase;

class GoldenRatioFraction extends ContinuedFraction {
  public function getA($n, $x) {
    return 1.0;
  }

  public function getB($n, $x) {
    return 1.0;
  }
}

class TanhFraction extends ContinuedFraction {
  public function getA($n, $x) {
    if ($n == 0) {
      return 0;
    } else {
      return $x * ($n * 2 - 1);
    }
  }

  public function getB($n, $x) {
    return 1.0;
  }
}

class ContinuedFractionTest extends PHPUnit_Framework_TestCase
{

    public function testGoldenRatio() {
        $cf = new GoldenRatioFraction();
        $this->assertEquals(1.61803399,
            $cf->evaluate(0.0, 1e-8), '', 1e-8);
    }

    public function testTanh() {
        $cf = new TanhFraction();
        $this->assertEquals(0.7615941560,
            $cf->evaluate(1.0, 1e-9), '', 1e-9);
        $this->assertEquals(0.4621171573,
            $cf->evaluate(2.0, 1e-9), '', 1e-9);
        $this->assertEquals(0.3215127375,
            $cf->evaluate(3.0, 1e-9), '', 1e-9);
    }
}
