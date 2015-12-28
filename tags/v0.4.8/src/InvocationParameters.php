<?php
/**
 * Description of interface providing request parameters.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;


/**
 * Provides access to request parameters.
 * They can be http query parameters, php cli parameters, etc..
 */
interface InvocationParameters
{
  /**
   * Returns value of parameter.
   *
   * @param string $key parameter name
   *
   * @returns mixed value of parameter
   * or null if such parameter wasn't specified.
   */
  public function getParameter($key);

  /**
   * Sets value of specific parameter.
   *
   * @param string $key parameter name
   * @param mixed $value
   *
   * @returns void
   */
  public function setParameter($key, $value);
}
