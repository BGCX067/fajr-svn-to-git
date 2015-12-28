<?php
/**
 * Tento súbor obsahuje objekt reprezentujúci príchodziu požiadavku
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
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
  private $input;
  private $time;

  /**
   * @param double time of the request as unix timestamp
   */
  public function __construct(InvocationParameters $input, $time)
  {
    $this->input = $input;
    $this->time = $time;
  }

  /**
   * Return a named parameter
   *
   * @param string $name
   * @param string defaultValue
   * @returns string parameter value
   */
  public function getParameter($name, $defaultValue = '')
  {
    // Note: if we want to change default from '' to null,
    // we must fix all controllers
    Preconditions::checkIsString($name, '$name should be string.');
    Preconditions::checkIsString($defaultValue,
        '$defaultValue should be string.');

    $value = $this->input->getParameter($name);
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
    Preconditions::checkIsString($name, '$name should be string.');
    return $this->input->getParameter($name) !== null;
  }

  /**
   * Ensure a parameter is not set after this call
   *
   * @param string $name parameter name to clear
   */
  public function clearParameter($name)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    $this->input->setParameter($name, null);
  }

  /**
   * @returns double time of the request as unix timestamp
   */
  public function getRequestTime()
  {
    return $this->time;
  }

}
