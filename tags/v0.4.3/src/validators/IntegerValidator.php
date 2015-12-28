<?php

namespace fajr\validators;

use fajr\exceptions\ValidationException;
use fajr\libfajr\base\Preconditions;

class IntegerValidator implements InputValidator
{
  private $signed;

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
