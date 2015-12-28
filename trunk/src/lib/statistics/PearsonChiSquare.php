<?php
/**
 * Contains Pearson's chi square test.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Statistics
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @see        http://en.wikipedia.org/wiki/Pearson's_chi-square_test
 * @filesource
 */

namespace fajr\lib\statistics;
use fajr\libfajr\base\Preconditions;
use fajr\lib\math\Math;
use RuntimeException;
use fajr\lib\math\Gamma;

/**
 * Compute Pearson's chi square test.
 *
 * Warning: please consult statistician for complete explanation
 * of what this test calculates and under which conditions.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Statistics
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @see        http://en.wikipedia.org/wiki/Pearson's_chi-square_test
 */
class PearsonChiSquare
{
  /**
   * Compute chiSquare test value from array of observations
   * and expected probabilities of slots.
   *
   * @param array(int) $observedCnt observed counts
   * @param array(double) $expectedProb expected probabilities
   *
   * @returns double chi squared
   */
  static function chiSquare(array $observedCnt, array $expectedProb)
  {
    Preconditions::check(count($observedCnt) == count($expectedProb),
        "number of slots for observations and expectations should match.");
    foreach ($observedCnt as $cnt) {
      Preconditions::check(is_int($cnt), "Observed count should be integer");
    }
    foreach ($expectedProb as $prob) {
      Preconditions::check(is_double($prob), "Probability should be double");
      Preconditions::check(0.0 < $prob && 1.0 > $prob,
          "Expected probabilities shoud be in range (0,1).");
    }
    Preconditions::check(abs(array_sum($expectedProb) - 1) < 1.0e-6,
        "Probabilities does not sum up to 1");

    $samples = array_sum($observedCnt);

    foreach ($expectedProb as $prob) {
      if ($samples * $prob <= 5) {
        // @see http://en.wikipedia.org/wiki/Pearson's_chi-square_test#Problems
        throw new RuntimeException("You should have more samples for " .
            "computing Pearson test with specified probabilities");
      }
    }
    $chisqr = 0;
    for ($i = 0; $i < count($observedCnt); $i++) {
      $expected = $expectedProb[$i] * $samples;
      $chisqr += Math::sqr($observedCnt[$i] - $expected) / $expected;
    }
    return $chisqr;
  }

  /**
   * Compute the p-value of  null-hypothesis holds.
   *
   * Warning: please read
   * http://en.wikipedia.org/wiki/P-value#Frequent_misunderstandings
   * or consult statistician how to interpret results.
   *
   * @param int $degreesOfFreedom If you have 1-D histogram
   *    analysis, $degreesOfFreedom should be number of bins-1.
   *    For other scenarios, please consult statistician.
   * @param double $chisqr result of chi-square test
   *
   * @returns double p-value.
   */
  static function pvalue($degreesOfFreedom, $chisqr) {
    Preconditions::check(is_int($degreesOfFreedom));
    Preconditions::check($degreesOfFreedom > 0);
    Preconditions::checkIsNumber($chisqr);
    Preconditions::check($chisqr >= 0);
    return Gamma::regularizedGammaQ($degreesOfFreedom / 2.0, $chisqr / 2.0);
  }
}

