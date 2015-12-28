<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains interface of all modules configuring dependency injector.
 *
 * @package    Fajr
 * @subpackage Injection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\injection;

use sfServiceContainer;
use sfServiceContainerAutoloader;

require_once '../third_party/symfony_di/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

/**
 * Interface for configuring dependency Injector.
 *
 * We are just wrapping Symfony framwork here.
 *
 * @package    Fajr
 * @subpackage Injection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface Module
{
  /**
   * Configure service container
   *
   * @param sfServiceContainer $container Container to be configured.
   *
   * @returns void
   */
  public function configure(sfServiceContainer $container);
}
