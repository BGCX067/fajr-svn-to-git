<?php

namespace fajr\validators;

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
