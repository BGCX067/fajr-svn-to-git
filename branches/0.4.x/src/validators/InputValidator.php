<?php
/**
 * The interface providing validation on input from user.
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

/**
 * Provides validation of user input.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface InputValidator
{
  /**
   * Ensure that input data are validated.
   *
   * @param string $data data to validate
   * @returns void
   * @throws ValidationException if input is not valid
   */
  public function validate($data);
}
