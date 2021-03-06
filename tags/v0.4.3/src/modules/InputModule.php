<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Injector module for Input
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\modules;

use fajr\FajrConfig;
use fajr\injection\Module;
use sfServiceContainerBuilder;
use sfServiceReference;
use fajr\validators\IntegerValidator;
use fajr\validators\StringValidator;
/**
 * Injector module for Input
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class InputModule implements Module
{
  /**
   * Configure injection of Input.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('Input.class', '\fajr\Input')
              ->addArgument('%Request.Parameters.GET%')
              ->addArgument('%Request.Parameters.POST%')
              ->addMethodCall('prepare', array())
              ->setShared(true);

    $container->setParameter('Request.Parameters.GET',
      array(
        'studium' => new IntegerValidator(false),
        'list' => new IntegerValidator(false),
        'predmet' => new IntegerValidator(false),
        'termin' => new IntegerValidator(false),
        'action' => new StringValidator(),
        // We need loginType in GET due to cosign proxy login!
        'loginType' => new StringValidator(),
        'serverName' => new StringValidator(),
      ));
    $container->setParameter('Request.Parameters.POST',
      array(
        'prihlasPredmetIndex' => new IntegerValidator(false),
        'prihlasTerminIndex' => new IntegerValidator(false),
        'odhlasIndex' => new IntegerValidator(false),
        'hash' => new StringValidator(),
        'action' => new StringValidator(),
        'login' => new StringValidator(),
        'password' => new StringValidator(),
        'cosignCookie' => new StringValidator(),
        'loginType' => new StringValidator(),
        'serverName' => new StringValidator(),
      ));
  }
}
