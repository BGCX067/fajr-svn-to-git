<?php

namespace fajr\validators;

use fajr\exceptions\ValidationException;

/**
 * Validator for validating choices from a predefined set of values
 */
class ChoiceValidator implements InputValidator
{

  private $choices;

  /**
   * Construct a new ChoiceValidator
   * @param array $choices array of allowed values
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
