<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains *basic* tests for SecureRandomOpensslProvider
 *
 * @package    Fajr
 * @subpackage Security
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\security;

use PHPUnit_Framework_TestCase;
use fajr\lib\statistics\PearsonChiSquare;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
require_once 'FrequencyDistributionHelper.php';

/**
 * @ignore
 */
class SecureRandomOpensslProviderTest extends PHPUnit_Framework_TestCase
{


  public function testLength() {
    if (!SecureRandomOpensslProvider::isAvailable()) {
      $this->markTestSkipped('OpenSSL not available');
    }

    $random = new SecureRandomOpensslProvider();

    $this->assertEquals(1, strlen($random->randomBytes(1)));
    $this->assertEquals(2, strlen($random->randomBytes(2)));
    $this->assertEquals(10, strlen($random->randomBytes(10)));
    $this->assertEquals(20000, strlen($random->randomBytes(20000)));
  }



  /**
   * Very limited basic checking on randomness. Checks only that
   * the resulting distribution of binary value of each byte is
   * approximately same.
   *
   * Warning: This is only basic test. Do not test quality of
   * random generators with it. It is supposed to be just test that
   * generator is working and does not have clear problems!
   */
  public function testBasicRandomness() {
    if (!SecureRandomOpensslProvider::isAvailable()) {
      $this->markTestSkipped('OpenSSL not available');
    }
    
    $random = new SecureRandomOpensslProvider();

    $this->assertGreaterThan(0.01,
        FrequencyDistributionHelper::pvalue($random->randomBytes(20000)),
        'Statistical test on randomness failed. This is probably not an error, try to re-run the test.');

    $this->assertGreaterThan(0.01,
        FrequencyDistributionHelper::pvalue($random->randomBytes(50000)),
        'Statistical test on randomness failed. This is probably not an error, try to re-run the test.');
  }

}
