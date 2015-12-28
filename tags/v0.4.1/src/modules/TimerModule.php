<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for Timer
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\injection\Module;
use sfServiceContainerBuilder;

/**
 * Injector module for Timer
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class TimerModule implements Module
{
  
  /** var double $initialTime to use in timer */
  private $initialTime;

  function __construct($initialTime)
  {
    $this->initialTime = $initialTime;
  }

    /**
   * Configure injection of Timer.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('Timer.class', '\fajr\libfajr\base\SystemTimer')
              ->addArgument($this->initialTime);
  }
}
