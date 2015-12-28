<?php
/**
 * Validates integer numbers.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */

namespace fajr\validators;

use fajr\exceptions\ValidationException;
use fajr\libfajr\base\Preconditions;

/**
 * Validates integer numbers.
 *
 * @package    Fajr
 * @subpackage Validators
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 */
class IntegerValidator implements InputValidator
{
  /** @var boolean allow signed integers */
  private $signed;

  /**
   * @param boolean $signed true if we allow signed integers
   */
  public function __construct($signed=true) {
    $this->signed = $signed;
  }

  public function validate($data)
  {
    Preconditions::checkIsString($data, '$data should be string.');
    if ((strlen($data)> 0) && ($data[0] == '-') && $this->signed) {
      $data = substr($data, 1);
    }

    if (!ctype_digit($data)) {
      throw new ValidationException("Číslo obsahuje neplatné znaky.");
    }
    if (strlen($data) >= 9) {
      throw new ValidationException("Číslo je príliš dlhé.");
    }
    return true;
  }

}
