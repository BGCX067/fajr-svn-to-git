<?php
/**
 * Injector module for DisplayManager.class
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

use fajr\FajrConfig;
use fajr\injection\Module;
use fajr\libfajr\base\Preconditions;
use fajr\rendering\Extension;
use sfServiceContainerBuilder;
use sfServiceReference;
use Twig_Environment;
use Twig_Extension_Escaper;
use Twig_Loader_Filesystem;

/**
 * Injector module for DisplayManager.class
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class DisplayManagerModule implements Module
{
  
  /**
   * Configure injection of DisplayManager.class
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('DisplayManager.class', '\fajr\DisplayManager')
              ->addArgument(new sfServiceReference('Twig_Environment.class'));

    $container->register('Twig_Loader_Filesystem.class',
                         'Twig_Loader_Filesystem')
              ->addArgument('%Twig.Template.Directory%');

    $container->register('Twig_Environment.class',
                         'Twig_Environment')
              ->addArgument(new sfServiceReference(
                                  'Twig_Loader_Filesystem.class'))
              ->addArgument('%Twig.Environment.options%')
              ->addMethodCall('addExtension',
                              array(new Twig_Extension_Escaper()))
              ->addMethodCall('addExtension',
                              array(new Extension()));

    $container->setParameter('Twig.Template.Directory',
                             FajrConfig::getDirectory('Template.Directory'));

    if (FajrConfig::get('Template.Cache')) {
      $cache = FajrConfig::getDirectory('Template.Cache.Path');
    } else {
      $cache = false;
    }

    $container->setParameter('Twig.Environment.options',
                      array(
                        'base_template_class' => '\fajr\rendering\Template',
                        'cache' => $cache,
                        'strict_variables' => true
                      ));
  }
}
