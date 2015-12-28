<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt reprezentujúci príchodziu požiadavku
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

/**
 * Class representing incoming request from browser
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Request
{

  /**
   * Return a named parameter
   *
   * @param string $name
   * @param string|null defaultValue
   * @returns string|null parameter value
   */
  public function getParameter($name, $defaultValue = '')
  {
    Preconditions::checkIsString($name, "name");
    if ($defaultValue != null) {
      Preconditions::checkIsString($defaultValue, 'defaultValue');
    }

    $value = Input::get($name);
    if ($value === null) {
      return $defaultValue;
    }
    return $value;
  }

  /**
   * Check if the request was called with a given parameter
   *
   * @param string $name parameter name
   * @returns bool true iff the parameter is present
   */
  public function hasParameter($name)
  {
    Preconditions::checkIsString($name, 'name');
    return Input::get($name) !== null;
  }

  /**
   * Ensure a parameter is not set after this call
   *
   * @param string $name parameter name to clear
   */
  public function clearParameter($name)
  {
    Preconditions::checkIsString($name, 'name');
    Input::set($name, null);
  }

}