<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
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
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\ServerConfig;

/**
 * Injector module for arguments passed to controllers.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ControllerInjectorModule implements Module
{
  /** @var AIS2ServerConnection */
  private $connection;
  /** @var ServerConfig */
  private $server;

  public function __construct(AIS2ServerConnection $connection, ServerConfig $server) {
    $this->connection = $connection;
    $this->server = $server;
  }

  /**
   * Configure injection of arguments of controllers
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('studium.controller.class', '\fajr\controller\studium\StudiumController')
              ->addArgument(new sfServiceReference('administracia_studia_screen.factory.class'))
              ->setShared(false);
    switch ($this->server->getBackendType()) {
      case ServerConfig::BACKEND_FAKE:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FakeFactoryImpl')
                  ->addArgument('%FakeDataDir.string%')
                  ->setShared(false);

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\fake\FakeMainScreen')
                  ->setShared(false);

        $container->setParameter('FakeDataDir.string',
            __DIR__.'/../regression/fake_data');
        break;
      case ServerConfig::BACKEND_LIBFAJR:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FactoryImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\AIS2MainScreenImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->setService('serverConnection.class', $this->connection);
        break;
      default:
        assert(false);
    }
  }
}
