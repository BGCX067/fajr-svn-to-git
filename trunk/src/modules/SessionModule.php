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
use fajr\config\FajrConfigOptions;
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
  private $config;

  public function __construct(FajrConfig $config) {
    $this->config = $config;
  }

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
              'session_cookie_secure' => $this->config->get(FajrConfigOptions::REQUIRE_SSL),
              'session_cookie_httponly' => true,
              'session_name' => 'fajr_session_id',
              );
    
    // this will render fajr usable  when running on localhost
    if ($_SERVER['HTTP_HOST'] == 'localhost')  {
        unset($options['session_cookie_domain']);
    }
    // cache expire, server
    ini_set("session.gc_maxlifetime", $lifeTimeSec);
    ini_set("session.cookie_lifetime", $lifeTimeSec);
    // custom cache expire is possible only for custom session directory
    session_save_path($this->config->getDirectory(FajrConfigOptions::PATH_TO_SESSIONS));
    // Note, we can't use setParameters as it will destroy previous values!
    $container->setParameter('session.options', $options);

    $container->register('Session.Storage.class', 'sfSessionStorage')
              ->addArgument('%session.options%')
              ->setShared(true);
  }
}
