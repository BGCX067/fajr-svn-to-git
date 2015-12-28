<?php
/**
 * Parameters passed by ?query part.
 *
 * @copyright  Copyright (c) 2010,2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;

use Exception;
use fajr\exceptions\SecurityException;
use fajr\exceptions\ValidationException;
use fajr\libfajr\base\IllegalStateException;
use fajr\libfajr\base\Preconditions;
use fajr\util\FajrUtils;
use fajr\validators\InputValidator;
use fajr\validators\IntegerValidator;
use fajr\validators\StringValidator;
use InvalidArgumentException;

/**
 * Provides access to parameters from http get/post request.
 */
class HttpInputParameters implements InvocationParameters
{
  /** @var array parsed values */
  private $inputParameters = array();

  /** @var array parsed GET values */
  private $_get = array();

  /** @var array parsed POST values */
  private $_post = array();

  /** @var bool did we called prepare? */
  private $prepared = false;
  private static $global_prepared = false;

  /** @var array(InputValidator) allowed GET parameters with validators */
  private $allowed_get;

  /** @var array(InputValidator) allowed POST parameters with validators */
  private $allowed_post;

  /**
   * Constructor.
   * 
   * Warning: Using more than one instance of HttpInputParameters may result
   * in unexpected behaviour.
   *
   * @param array(string=>InputValidator) $allowed_get allowed GET parameters
   *    with validators.
   * @param array(string=>InputValidator) $allowed_post allowed POST parameters
   *    with validators.
   */
  public function __construct(array $allowed_get, array $allowed_post)
  {
    $to_check = array_merge(array_values($allowed_get),
                            array_values($allowed_post));
    foreach ($to_check as $item) {
      if (!($item instanceOf InputValidator)) {
        throw new InvalidArgumentException();
      }
    }
    $this->allowed_get = $allowed_get;
    $this->allowed_post = $allowed_post;
  }

  /**
   * Validate $data according to validators and return only
   * allowed parameters.
   *
   * @param array $data
   * @param array(string=>InputValidator) $validators
   *
   * @returns array of allowed and validated parameters
   */
  private function _prepare_array(array $data, array $validators)
  {
    $result = array();
    foreach ($validators as $name => $validator) {
      if (isset($data[$name])) {
        try {
          $validator->validate($data[$name]);
        } catch (ValidationException $e) {
          throw new SecurityException("Problem validating argument '$name' " .
              "with value '" . $data[$name] . "'.", 0, $e);
        }
        $result[$name] = $data[$name];
      }
    }
    return $result;
  }

  /**
   * Prepare HttpInputParameters.
   *
   * You must call this function exactly once
   * (globally for any instance) before any other methods.
   */
  public function prepare()
  {
    if ($this->prepared) {
      throw new IllegalStateException(
          "Input::prepare should be called only once per request.");
    }
    if (self::$global_prepared) {
      throw new IllegalStateException(
          "Input::prepare should be called only once per request.");
    }
    self::$global_prepared = true;
    $this->prepared = true;

    $_get = $_GET;
    $_post = $_POST;

    // budeme pouzivat uz len Input
    unset($_GET);
    unset($_POST);

    $this->_get = $this->_prepare_array($_get, $this->allowed_get);
    $this->_post = $this->_prepare_array($_post, $this->allowed_post);

    $get_keys = array_keys($this->_get);
    $post_keys = array_keys($this->_post);
    if (array_intersect($get_keys, $post_keys)) {
      throw new SecurityException("Same parameter through get and post.");
    }

    $this->inputParameters = array_merge($this->_get, $this->_post);
  }

  /**
   * Returns value of parameter identified by $key
   *
   * @param string $key
   *
   * @returns string value or null if no such query parameter was specified
   */
  public function getParameter($key)
  {
    $this->assertInitialized();
    Preconditions::checkIsString($key);
    if (!isset($this->inputParameters[$key])) {
      return null;
    }
    else {
      return $this->inputParameters[$key];
    }
  }

  /**
   * Sets value of parameter identified by $key
   *
   * @param string $key
   * @param mixed $value
   *
   * @returns void
   */
  public function setParameter($key, $value)
  {
    $this->assertInitialized();
    Preconditions::checkIsString($key);
    $this->inputParameters[$key] = $value;
  }

  /**
   * Checks whether we have called prepare()
   *
   * @throws IllegalStateException
   */
  private function assertInitialized()
  {
    if (!$this->prepared) {
      throw new IllegalStateException("You must call prepare() first.");
    }
  }
}
?>
