<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains class that runs callback on destruction.
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\base;

/**
 * Closure class represents "cleaner". It is used to run callbacks
 * in its own destruction.
 *
 * Warnings: Be careful with references to ClosureRunner
 * (otherwise it may be destroyed at unpredictable times)
 * and also note, that if you pass class callback to ClosureRunner,
 * the class won't be destroyed before ClosureRunner.
 *
 * Example usage:
 * <code>
 * <?php
 * function do_something_with_object() {
 *  $closure = new ClosureRunner(array($this, 'cleanInternalState'),
 *                               some, arguments, to, cleanInternalState')
 *  $closure2 = new ClosureRunner('end_session');
 *  now do heavy work with possible exceptions, returns and other magic
 * }
 * 
 * do_something_with_object();
 * // $this->cleanInternalState(some,arguments,to,cleanInternalState') and
 * // end_session() will be called automatically whatever you do
 * ?>
 * </code> 
 */
class ClosureRunner
{
  /** @var array $args arguments to the callback */
  private $args = null;

  /** @var callback $callback */
  private $callback = null;

  /**
   * Construct the closure
   *
   * @param callback      $callback function to call on destruction
   * @param argument_list $var_args arguments to pass to closure callback
   */
  public function __construct($callback)
  {
    $this->callback = $callback;
    $this->args = func_get_args();
    // Warning: we can't call func_get_args as a parameter to array_shift!
    array_shift($this->args);
  }

  /**
   * Destructor. Runs the callback.
   */
  public function __destruct()
  {
    call_user_func_array($this->callback, $this->args);
  }

}
