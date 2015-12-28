<?php
/**
 * Contains useful math functions
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
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr__Lib__Math
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\lib\math;

/**
 * This is a utility class that provides computation methods related to the
 * Gamma family of functions.
 *
 * @version $Revision: 233121 $ $Date: 2005-08-16 21:41:02 -0700 (Tue, 16 Aug 2005) $
 */
class Gamma
{
    /** Maximum allowed numerical error. */
    const DEFAULT_EPSILON = 10e-9;

    private static $lanczos = array( 0.99999999999999709182,
            57.156235665862923517, -59.597960355475491248,
            14.136097974741747174, -0.49191381609762019978,
            .33994649984811888699e-4, .46523628927048575665e-4,
            -.98374475304879564677e-4, .15808870322491248884e-3,
            -.21026444172410488319e-3, .21743961811521264320e-3,
            -.16431810653676389022e-3, .84418223983852743293e-4,
            -.26190838401581408670e-4, .36899182659531622704e-5, );


    /** Avoid repeated computation of log of 2 PI in logGamma
     *  HALF_LOG_2_PI = 0.5 * Math.log(2.0 * Math.PI);
     */
    const HALF_LOG_2_PI = 0.918938533204672741780329736406;

    /**
     * Returns the natural logarithm of the gamma function &#915;(x).
     *
     * The implementation of this method is based on:
     * <ul>
     * <li><a href="http://mathworld.wolfram.com/GammaFunction.html">
     * Gamma Function</a>, equation (28).</li>
     * <li><a href="http://mathworld.wolfram.com/LanczosApproximation.html">
     * Lanczos Approximation</a>, equations (1) through (5).</li>
     * <li><a href="http://my.fit.edu/~gabdo/gamma.txt">Paul Godfrey, A note on
     * the computation of the convergent Lanczos complex Gamma approximation
     * </a></li>
     * </ul>
     * 
     * @param x the value.
     * @return log(&#915;(x))
     */
    public static function logGamma($x) {
        if (is_nan($x) || ($x <= 0.0)) {
          return NAN;
        };
        $g = 607.0 / 128.0;

        $sum = 0.0;
        for ($i = count(self::$lanczos) - 1; $i > 0; --$i) {
            $sum = $sum + (self::$lanczos[$i] / ($x + $i));
        }
        $sum = $sum + self::$lanczos[0];

        $tmp = $x + $g + .5;
        $ret = (($x + .5) * log($tmp)) - $tmp + self::HALF_LOG_2_PI
                + log($sum / $x);
        return $ret;
    }

    /**
     * Returns the regularized gamma function P(a, x).
     * 
     * The implementation of this method is based on:
     * <ul>
     * <li>
     * <a href="http://mathworld.wolfram.com/RegularizedGammaFunction.html">
     * Regularized Gamma Function</a>, equation (1).</li>
     * <li>
     * <a href="http://mathworld.wolfram.com/IncompleteGammaFunction.html">
     * Incomplete Gamma Function</a>, equation (4).</li>
     * <li>
     * <a href="http://mathworld.wolfram.com/ConfluentHypergeometricFunctionoftheFirstKind.html">
     * Confluent Hypergeometric Function of the First Kind</a>, equation (1).
     * </li>
     * </ul>
     * 
     * @param a the a parameter.
     * @param x the value.
     * @param epsilon When the absolute value of the nth item in the
     *                series is less than epsilon the approximation ceases
     *                to calculate further elements in the series.
     * @param maxIterations Maximum number of "iterations" to complete. 
     * @return the regularized gamma function P(a, x)
     * @throws MathException if the algorithm fails to converge.
     */
    public static function regularizedGammaP($a, $x,
            $epsilon = self::DEFAULT_EPSILON, $maxIterations = PHP_INT_MAX) {
        if (is_nan($a) || is_nan($x) || ($a <= 0.0)
                || ($x < 0.0)) {
            $ret = NAN;
        } else if ($x == 0.0) {
            $ret = 0.0;
        } else if ($a >= 1.0 && $x > $a) {
            // use regularizedGammaQ because it should converge faster in this
            // case.
            $ret = 1.0 - self::regularizedGammaQ($a, $x, $epsilon, $maxIterations);
        } else {
            // calculate series
            $n = 0.0; // current element index
            $an = 1.0 / $a; // n-th element in the series
            $sum = $an; // partial sum
            while (abs($an) > $epsilon && $n < $maxIterations) {
                // compute next element in the series
                $n = $n + 1.0;
                $an = $an * ($x / ($a + $n));

                // update partial sum
                $sum = $sum + $an;
            }
            if ($n >= $maxIterations) {
                throw new MathException(
                        "maximum number of iterations reached");
            } else {
                $ret = exp(-$x + ($a * log($x)) - self::logGamma($a))
                        * $sum;
            }
        }

        return $ret;
    }

    /**
     * Returns the regularized gamma function Q(a, x) = 1 - P(a, x).
     * 
     * The implementation of this method is based on:
     * <ul>
     * <li>
     * <a href="http://mathworld.wolfram.com/RegularizedGammaFunction.html">
     * Regularized Gamma Function</a>, equation (1).</li>
     * <li>
     * <a href="    http://functions.wolfram.com/GammaBetaErf/GammaRegularized/10/0003/">
     * Regularized incomplete gamma function: Continued fraction representations  (formula 06.08.10.0003)</a></li>
     * </ul>
     * 
     * @param a the a parameter.
     * @param x the value.
     * @param epsilon When the absolute value of the nth item in the
     *                series is less than epsilon the approximation ceases
     *                to calculate further elements in the series.
     * @param maxIterations Maximum number of "iterations" to complete. 
     * @return the regularized gamma function P(a, x)
     * @throws MathException if the algorithm fails to converge.
     */
    public static function regularizedGammaQ($a, $x,
            $epsilon = self::DEFAULT_EPSILON, $maxIterations = PHP_INT_MAX)
    {
        if (is_nan($a) || is_nan($x) || ($a <= 0.0)
                || ($x < 0.0)) {
            $ret = NAN;
        } else if ($x == 0.0) {
            $ret = 1.0;
        } else if ($x < $a || $a < 1.0) {
            // use regularizedGammaP because it should converge faster in this
            // case.
            $ret = 1.0 - self::regularizedGammaP($a, $x, $epsilon, $maxIterations);
        } else {
            // create continued fraction
            $cf = new RegularizedGammaQFraction($a);

            $ret = 1.0 / $cf->evaluate($x, $epsilon, $maxIterations);
            $ret = exp(-$x + ($a * log($x)) - self::logGamma($a)) * $ret;
        }

        return $ret;
    }
}

class RegularizedGammaQFraction extends ContinuedFraction
{
  private $a;

  public function __construct($a) {
    $this->a = $a;
  }

  protected function getA($n, $x) {
    return ((2.0 * $n) + 1.0) - $this->a + $x;
  }

  protected function getB($n, $x) {
    return $n * ($this->a - $n);
  }
}
