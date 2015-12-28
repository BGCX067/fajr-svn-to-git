<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for SessionInitializer
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\FajrConfig;
use fajr\injection\Module;
use sfServiceContainer;

/**
 * Injector module for SessionInitializer.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class SessionInitializerModule implements Module
{
  /**
   * Configure injection of SessionInitializer.class
   *
   * @param sfServiceContainer $container Symfony container to configure
   */
  public function configure(sfServiceContainer $container)
  {
    $container->register('SessionInitializer.class', 'fajr\SessionInitializer')
              ->addArgument('%session.life_time_sec%')
              ->addArgument('%session.save_path%')
              ->addArgument('%session.path%')
              ->addArgument('%session.domain%');
    $container->setParameters(
        array('session.life_time_sec' => 36000,
              'session.save_path' =>
                  FajrConfig::getDirectory('Path.Temporary.Sessions'),
              'session.path' => '/',
              'session.domain' => '.' . $_SERVER['HTTP_HOST'],
              )
        );
  }
}
