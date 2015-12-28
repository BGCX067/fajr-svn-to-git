<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for Trace.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\injection\Module;
use sfServiceContainer;

/**
 * Injector module for Trace.class, injects NullTrace
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TraceModule implements Module
{
  /**
   * Configures Trace.class for injection.
   *
   * @param sfServiceContainer $container Container to configure
   */
  public function configure(sfServiceContainer $container)
  {
    $container->register('Trace.class', 'fajr\libfajr\pub\base\NullTrace');
  }
}
