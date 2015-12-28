<?php
/**
 * Validates if input is in the set of predefined values.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr\validators;

use fajr\exceptions\ValidationException;

/**
 * Validator for validating choices from a predefined set of values
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class ChoiceValidator implements InputValidator
{
  /** @var array(mixed) predefined choices which are allowed */
  private $choices;

  /**
   * Construct a new ChoiceValidator
   * @param array(mixed) $choices array of allowed values
   */
  public function __construct(array $choices) {
    $this->choices = $choices;
  }

  /**
   * Check if $data is contained in choices array
   *
   * Note that this validator uses strict compare ( === )
   *
   * @param mixed $data
   * @returns void
   * @throws ValidationException if $data is not contained in array
   */
  public function validate($data)
  {
    if (array_search($data, $this->choices, true) === false) {
      throw new ValidationException("Value not allowed");
    }
    return true;
  }

}
