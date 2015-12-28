<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for Statistics
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\injection\Module;
use sfServiceContainerBuilder;
use sfServiceReference;

/**
 * Injector module for Statistics
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class StatisticsModule implements Module
{
  
  /**
   * Configure injection of Statistics.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('Statistics.class', '\fajr\Statistics')
              ->addArgument(new sfServiceReference('Timer.class'));
  }
}
