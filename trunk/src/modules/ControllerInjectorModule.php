<?php
/**
 * Injector module for arguments passed to controllers.
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

use fajr\injection\Module;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\config\ServerConfig;
use sfServiceContainerBuilder;
use sfServiceReference;
use sfStorage;

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

  /** @var sfStorage */
  private $storage;

  public function __construct(AIS2ServerConnection $connection,
      ServerConfig $server, sfStorage $storage) {
    $this->connection = $connection;
    $this->server = $server;
    $this->storage = $storage;
  }

  /**
   * Configure injection of arguments of controllers
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('studium.controller.class',
                         '\fajr\controller\studium\StudiumController')
              ->addArgument(new sfServiceReference('administracia_studia_screen.factory.class'))
              ->addArgument('%serverTime%')
              ->setShared(false);

    $container->register('predmety.controller.class',
                         '\fajr\controller\predmety\PredmetyController')
              ->addArgument(new sfServiceReference('register_predmetov_screen.factory.class'))
              ->addArgument('%serverTime%')
              ->setShared(false);

    switch ($this->server->getBackendType()) {
      case ServerConfig::BACKEND_FAKE:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FakeFactoryImpl')
                  ->addArgument(new sfServiceReference('fake.storage.class'))
                  ->setShared(false);

        $container->register('register_predmetov_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSST060_register_predmetov\VSST060_FakeFactoryImpl')
                  ->addArgument(new sfServiceReference('fake.storage.class'))
                  ->setShared(false);

        $container->register('fake.storage.class', '\fajr\libfajr\storage\TemporarilyModifiableStorage')
                  ->addArgument('%TempStorage.options%');

        $container->setParameter('TempStorage.options',
                    array('permanent_storage' => new sfServiceReference('fake.file.storage.class'),
                          'temporary_storage' => $this->storage));

        $container->register('fake.file.storage.class', '\fajr\libfajr\storage\FileStorage')
                  ->addArgument('%Fake.FileStorage.options%');

        $container->setParameter('Fake.FileStorage.options',
                    array('root_path' => \fajr\libfajr\pub\regression\fake_data\FakeData::getDirectory()));

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\fake\FakeMainScreen')
                  ->setShared(false);

        /**
         * Somewhat arbitrary fixed as 10.1.2011 12:17:53
         **/
        $FAKE_TIME = mktime(12, 17, 53, 1, 10, 2011);

        $container->setParameter('serverTime', $FAKE_TIME);
        break;
      case ServerConfig::BACKEND_LIBFAJR:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FactoryImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->register('register_predmetov_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSST060_register_predmetov\VSST060_FactoryImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\AIS2MainScreenImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->setService('serverConnection.class', $this->connection);

        $container->setParameter('serverTime', time());
        break;
      default:
        assert(false);
    }
  }
}
