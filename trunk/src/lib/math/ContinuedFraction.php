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
 * Provides a generic means to evaluate continued fractions. Subclasses simply
 * provided the a and b coefficients to evaluate the continued fraction.
 *
 * fraction = a0/b0 + 1 / (a1/b1 + 1/(a2/a2 + 1/(a3/b3 + ... )))
 *
 * <p>
 * References:
 * <ul>
 * <li><a HREF="http://mathworld.wolfram.com/ContinuedFraction.html">
 * Continued Fraction</a></li>
 * </ul>
 * </p>
 *
 * @version $Revision$ $Date: 2005-02-26 05:11:52 -0800 (Sat, 26 Feb 2005) $
 */
abstract class ContinuedFraction {
    /** Maximum allowed numerical error. */
    const DEFAULT_EPSILON = 10e-9;

    /**
     * Access the n-th a coefficient (numerator) of the continued fraction. Since a can be
     * a function of the evaluation point, x, that is passed in as well.
     * @param int $n the coefficient index to retrieve.
     * @param double $x the evaluation point.
     * @returns double the n-th a coefficient.
     */
    protected abstract function getA($n, $x);

    /**
     * Access the n-th b coefficient(denumerator) of the continued fraction. Since b can be
     * a function of the evaluation point, x, that is passed in as well.
     * @param int $n the coefficient index to retrieve.
     * @param double $x the evaluation point.
     * @returns double the n-th b coefficient.
     */
    protected abstract function getB($n, $x);

    /**
     * Evaluates the continued fraction at the value x.
     * 
     * The implementation of this method is based on:
     * <ul>
     * <li>O. E-gecio-glu, C . K. Koc, J. Rifa i Coma,
     * <a HREF="http://citeseer.ist.psu.edu/egecioglu91fast.html">
     * On Fast Computation of Continued Fractions</a>, Computers Math. Applic.,
     * 21(2--3), 1991, 167--169.</li>
     * </ul>
     * 
     * @param double $x the evaluation point.
     * @param double $epsilon maximum error allowed.
     * @param int $maxIterations maximum number of convergents
     *
     * @returns double the value of the continued fraction evaluated at x.
     * @throws MathException if the algorithm fails to converge.
     */
    public function evaluate($x, $epsilon = self::DEFAULT_EPSILON, $maxIterations = PHP_INT_MAX)
    {
      $f = array(); // 2x2
      $a = array(); // 2x2
      $an = array(); // 2x2

      $a[0][0] = $this->getA(0, $x);
      $a[0][1] = 1.0;
      $a[1][0] = 1.0;
      $a[1][1] = 0.0;

      return $this->_evaluate(1, $x, $a, $an, $f, $epsilon, $maxIterations);
    }

    /**
     * Evaluates the n-th convergent, fn = pn / qn, for this continued fraction
     * at the value x.
     * @param int $n the convergent to compute.
     * @param double $x the evaluation point.
     * @param double[2][2] $a (n-1)-th convergent matrix. (Input)
     * @param double[2][2] $an the n-th coefficient matrix. (Output)
     * @param double[2][2] $f the n-th convergent matrix. (Output)
     * @param double $epsilon maximum error allowed.
     * @param int $maxIterations maximum number of convergents
     * @returns double the value of the the n-th convergent for this continued fraction
     * evaluated at x. 
     * @throws MathException if the algorithm fails to converge.
     */
    private function _evaluate(
        $n, $x, $a, $an, $f, $epsilon, $maxIterations)
    {
        // create next matrix
        $an[0][0] = $this->getA($n, $x);
        $an[0][1] = 1.0;
        $an[1][0] = $this->getB($n, $x);
        $an[1][1] = 0.0;

        // multiply a and an, save as f
        $f[0][0] = ($a[0][0] * $an[0][0]) + ($a[0][1] * $an[1][0]);
        $f[0][1] = ($a[0][0] * $an[0][1]) + ($a[0][1] * $an[1][1]);
        $f[1][0] = ($a[1][0] * $an[0][0]) + ($a[1][1] * $an[1][0]);
        $f[1][1] = ($a[1][0] * $an[0][1]) + ($a[1][1] * $an[1][1]);

        // determine if we're close enough
        if (abs(($f[0][0] * $f[1][1]) - ($f[1][0] * $f[0][1])) <
            abs($epsilon * $f[1][0] * $f[1][1]))
        {
            $ret = $f[0][0] / $f[1][0];
        } else {
            if ($n >= $maxIterations) {
                throw new MathException(
                    "Continued fraction convergents failed to converge.");
            }
            // compute next
          $ret = self::_evaluate($n + 1, $x, $f /* new a */
            , $an /* reuse an */
            , $a /* new f */
            , $epsilon, $maxIterations);
        }

        return $ret;
    }
}
