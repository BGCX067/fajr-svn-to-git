<?php
/**
 * Validates any string.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\validators;

use fajr\exceptions\ValidationException;
use fajr\libfajr\base\Preconditions;

/**
 * Validates any string.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class StringValidator implements InputValidator
{

  public function validate($data)
  {
    Preconditions::checkIsString($data, '$data should be string.');
    return true;
  }

}
