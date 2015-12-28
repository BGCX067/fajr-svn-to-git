<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for Context
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\FajrConfig;
use fajr\injection\Module;
use sfServiceContainerBuilder;
use sfServiceReference;

/**
 * Injector module for Context
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class ContextModule implements Module
{
  /**
   * Configure injection of Contexts.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('Request.class', '\fajr\Request');
    $container->register('Response.class', '\fajr\Response');
    $container->register('Context.class', '\fajr\Context')
              ->addMethodCall('setRequest', array(new sfServiceReference('Request.class')))
              ->addMethodCall('setResponse', array(new sfServiceReference('Response.class')));
  }
}
