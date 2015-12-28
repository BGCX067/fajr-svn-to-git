<?php
/**
 * Injector module for session storage
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\config\FajrConfig;
use fajr\injection\Module;
use sfServiceContainerBuilder;

/**
 * Injector module for session storage.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class SessionModule implements Module
{
  /**
   * Configure injection of SessionInitializer.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $lifeTimeSec = 36000;
    $options = 
        array('session_cookie_lifetime' => $lifeTimeSec,
              'session_cookie_path' => '/',
              'session_cookie_domain' => '.' . $_SERVER['HTTP_HOST'],
              'session_cookie_secure' => FajrConfig::get('SSL.Require'),
              'session_cookie_httponly' => true,
              'session_name' => 'fajr_session_id',
              );
    // cache expire, server
    ini_set("session.gc_maxlifetime", $lifeTimeSec);
    ini_set("session.cookie_lifetime", $lifeTimeSec);
    // custom cache expire is possible only for custom session directory
    session_save_path(FajrConfig::getDirectory('Path.Temporary.Sessions'));
    // Note, we can't use setParameters as it will destroy previous values!
    $container->setParameter('session.options', $options);

    $container->register('Session.Storage.class', 'sfSessionStorage')
              ->addArgument('%session.options%')
              ->setShared(true);
  }
}
