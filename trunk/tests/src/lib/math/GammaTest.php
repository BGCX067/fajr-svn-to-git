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

class GammaTest extends PHPUnit_Framework_TestCase
{
    public function testRegularizedGammaNanPositive()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::regularizedGammaP(NAN, 1.0)));
      $this->assertTrue(is_nan(Gamma::regularizedGammaQ(NAN, 1.0)));
    }

    public function testRegularizedGammaPositiveNan()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::regularizedGammaP(1.0, NAN)));
      $this->assertTrue(is_nan(Gamma::regularizedGammaQ(1.0, NAN)));
    }

    public function testRegularizedGammaNegativePositive()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::regularizedGammaP(-1.5, 1.0)));
      $this->assertTrue(is_nan(Gamma::regularizedGammaq(-1.5, 1.0)));
    }

    public function testRegularizedGammaPositiveNegative()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::regularizedGammaP(1.0, -1.0)));
      $this->assertTrue(is_nan(Gamma::regularizedGammaq(1.0, -1.0)));
    }

    public function testRegularizedGammaZeroPositive()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::regularizedGammaP(0.0, 1.0)));
      $this->assertTrue(is_nan(Gamma::regularizedGammaq(0.0, 1.0)));
    }

    public function testRegularizedGammaPositiveZero()
    {
      $this->assertEquals(0.0, Gamma::regularizedGammaP(1.0, 0.0), '', 10e-10);
      $this->assertEquals(1.0, Gamma::regularizedGammaQ(1.0, 0.0), '', 10e-10);
    }

    public function testRegularizedGammaPositivePositive()
    {
      $this->assertEquals(0.6321205588, Gamma::regularizedGammaP(1.0, 1.0), '', 1e-8);
      $this->assertEquals(1 - 0.6321205588, Gamma::regularizedGammaQ(1.0, 1.0), '', 1e-8);
    }

    public function testRegularizedGammaPositivePositive2()
    {
      $this->assertEquals(0.05265301734, Gamma::regularizedGammaP(5.0, 2.0), '', 1e-8);
      $this->assertEquals(1 - 0.05265301734, Gamma::regularizedGammaQ(5.0, 2.0), '', 1e-8);
    }

    public function testLogGammaNan()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::logGamma(NAN)));
    }

    public function testLogGammaNegative()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::logGamma(-1.0)));
    }

    public function testLogGammaZero()
    {
      // Note: you can't use assertEquals, because it doesn't work with NANs
      $this->assertTrue(is_nan(Gamma::logGamma(0.0)));
    }

    public function testLogGammaOne()
    {
      $this->assertEquals(0.0, Gamma::logGamma(1.0), '', 1e-9);
    }

    public function testLogGammaPositive()
    {
      $this->assertEquals( 0.6931471806, Gamma::logGamma(3.0), '', 1e-9);
      $this->assertEquals(71.2570389672, Gamma::logGamma(30.0), '', 1e-9);
      $this->assertEquals(-0.0853740900, Gamma::logGamma(1.2), '', 1e-9);
    }
}
