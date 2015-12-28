<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains mock of InvocationParams.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\libfajr\base\Preconditions;

/**
 * Mock version of InvocationParams
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class MockInvocationParameters implements InvocationParameters {

  /** @var array(string=>string) saved data */
  private $data;

  public function __construct()
  {
    $this->data = array();
  }

  /**
   * Returns parameter value.
   *
   * @param string $key parameter name
   *
   * @returns mixed parameter value
   */
  public function getParameter($key)
  {
    Preconditions::checkIsString($key);
    if (!array_key_exists($key, $this->data)) {
      return null;
    }
    return $this->data[$key];
  }

  /**
   * Set value of parameter.
   *
   * @param string $key key to the data
   * @param mixed $data
   *
   * @returns void
   */
  public function setParameter($key, $data)
  {
    Preconditions::checkIsString($key);
    $this->data[$key] = $data;
  }
}
