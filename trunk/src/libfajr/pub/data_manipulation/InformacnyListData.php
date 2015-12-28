<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\data_manipulation;

interface InformacnyListData
{
  /**
   * Returns value of chosen attribute.
   *
   * @param string $member Name of requested attribute.
   * @returns string|false Value of requested attribute or false on failure.
   */
  public function getAttribute($attribute);

  /**
   * Detects existence of chosen attribute.
   *
   * @param string $member Name of requested attribute.
   * @returns bool True if requested attribute exists.
   */
  public function hasAttribute($attribute);

  /**
   * Returns values of all attribute.
   *
   * @returns array Array containing attribute names as keys and
   *                attribute values as string values.
   */
  public function getAllAttributes();

  /**
   * Returns array of existing attributes.
   *
   * @returns array Array containing all available attribute names.
   */
  public function getListOfAttributes();
}
