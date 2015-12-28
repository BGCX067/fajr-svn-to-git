<?php
/**
 * This file contains tests for math utils.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Math
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use fajr\lib\math\Math;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class MathTest extends PHPUnit_Framework_TestCase
{
  public function testSqr()
  {
    $this->assertSame(0, Math::sqr(0));
    $this->assertSame(1, Math::sqr(-1));
    $this->assertSame(1, Math::sqr(1));
    $this->assertSame(4, Math::sqr(2));
    $this->assertSame(4.0, Math::sqr(2.0));
    $this->assertEquals(1.7689, Math::sqr(1.33), '', 1e-10);
  }

}
