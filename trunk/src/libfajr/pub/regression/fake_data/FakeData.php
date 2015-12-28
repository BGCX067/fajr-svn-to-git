<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Used to locate directory of fake data;
 *
 * @package    Fajr
 * @subpackage Regression__Fake_data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\pub\regression\fake_data;

/**
 * It is here just to know the location of fake data.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class FakeData
{
  /**
   * @returns string this directory
   */
  public static function getDirectory()
  {
    return __DIR__;
  }
}
