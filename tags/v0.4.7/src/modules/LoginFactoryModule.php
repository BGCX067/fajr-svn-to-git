<?php
/**
 * Injector module for LoginFactory.class
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
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
 * Injector module for LoginFactory.class
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class LoginFactoryModule implements Module
{
  
  /**
   * Configure injection of LoginFactory.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('LoginFactory.class', '\fajr\libfajr\pub\login\LoginFactoryImpl');
  }
}
