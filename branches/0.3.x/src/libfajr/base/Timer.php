<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Interface wrapping timers.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\base;

/**
 * Simple timer which can measure elapsed time.
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface Timer
{
  /**
   * Start counting time from this moment.
   *
   * @returns void
   */
  public function reset();

  /**
   * Get time in seconds elapsed from last resetting.
   * Note that calling this function does not reset timer.
   *
   * @returns double elapsed time
   */
  public function getElapsedTime();
}
