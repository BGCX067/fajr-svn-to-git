<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Description of Input
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
use fajr\validators\InputValidator;
use fajr\validators\IntegerValidator;
use fajr\validators\StringValidator;
use InvalidArgumentException;

class Input
{
  private $inputParameters = array();
  private $_get = array();
  private $_post = array();
  private static $prepared = false;
  private $allowed_get;
  private $allowed_post;

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

  private function _prepare_array($data, $validators)
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

  public function prepare()
  {
    if (self::$prepared) {
      throw new IllegalStateException(
          "Input::prepare should be called only once per request.");
    }
    self::$prepared = true;

    if (FajrConfig::get('URL.Path')) {
      $_get = array_merge(FajrRouter::pathToParams(FajrUtils::pathInfo()),
                          $_GET);
    } else {
      $_get = $_GET;
    }
    $_post = $_POST;
    // budeme pouzivat uz len Input
    unset($_GET);
    unset($_POST);

    $this->_get = $this->_prepare_array($_get, $this->allowed_get);
    $this->_post = $this->_prepare_array($_post, $this->allowed_post);

    $this->inputParameters = array_merge($this->_get, $this->_post);
  }

  public function get($key = null)
  {
    $this->assertInitialized();
    if ($key === null) {
      // TODO(ppershing): is this needed?
      return $this->inputParameters;
    }
    if (!isset($this->inputParameters[$key])) {
      return null;
    }
    else {
      return $this->inputParameters[$key];
    }
  }
  
  public function set($key, $value)
  {
    $this->assertInitialized();
    $this->inputParameters[$key] = $value;
  }
  
  public function getUrlParams()
  {
    // todo(ppershing): is this needed?
    $this->assertInitialized();
    return $this->_get;
  }

  private function assertInitialized()
  {
    if (!self::$prepared) {
      throw new IllegalStateException("You must call prepare() first.");
    }
  }
}
?>
