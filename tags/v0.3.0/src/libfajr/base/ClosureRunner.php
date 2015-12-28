<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\base;

/**
 * Closure class represents "cleaner". It is used to run callbacks
 * in its own destruction.
 * TODO: Warnings.
 */
class ClosureRunner
{
  private $args = null;
  private $callback = null;
  /**
   * Construct the closure
   *
   * @param function  $callback function to call on destruction
   * @param $var_args arguments to pass to closure callback
   */
  public function __construct($callback)
  {
    $this->callback = $callback;
    $this->args = func_get_args();
    // Warning: we can't call func_get_args as a parameter to array_shift!
    array_shift($this->args);
  }

  public function __destruct()
  {
    call_user_func_array($this->callback, $this->args);
  }

}
