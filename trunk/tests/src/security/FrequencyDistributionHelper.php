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

use fajr\lib\statistics\PearsonChiSquare;

class FrequencyDistributionHelper {

  /**
   * Checks that resulting distribution of binary values is
   * approximately same.
   *
   * @param string $bytes to check
   */
  public static function pvalue($bytes) {
    $hist = array(); // histogram
    $freq = array(); // expected frequency
    $N = 256;
    for ($i = 0; $i < $N; $i++) {
      $hist[$i] = 0;
      $freq[$i] = 1.0 / $N;
    }

    foreach (unpack('C*', $bytes) as $char) {
      $hist[$char]++;
    }

    $chisqr = PearsonChiSquare::chiSquare($hist, $freq);
    return PearsonChiSquare::pvalue($N - 1, $chisqr);
  }
}
