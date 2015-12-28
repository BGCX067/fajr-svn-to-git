<?php
/**
 * Injector module for Controller.class
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
 * Injector module for Controller.class
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class ControllerModule implements Module
{
  /**
   * Configure injection of Controller.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('Controller.class', '\fajr\controller\DispatchController')
              ->addArgument('%controller.dispatchMap%')
              ->setShared(false);
    $parameters = 
        array('controller.dispatchMap' => array(
                  'studium' => 'studium.controller.class',
                  'predmety' => 'predmety.controller.class',
                  'userSettings' => 'userSettings.controller.class',
                  ),
              );
    $container->addParameters($parameters);
  }
}
