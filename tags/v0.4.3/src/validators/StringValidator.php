<?php

namespace fajr\validators;

use fajr\exceptions\ValidationException;
use fajr\libfajr\base\Preconditions;

class StringValidator implements InputValidator
{

  public function validate($data)
  {
    Preconditions::checkIsString($data, '$data should be string.');
    return true;
  }

}
