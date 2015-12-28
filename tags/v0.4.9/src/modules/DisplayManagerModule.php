<?php
/**
 * Injector module for DisplayManager.class
 *
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\config\FajrConfig;
use fajr\injection\Module;
use fajr\libfajr\base\Preconditions;
use fajr\rendering\Extension;
use fajr\rendering\TwigFactory;
use sfServiceContainerBuilder;
use sfServiceReference;
use Twig_Environment;
use Twig_Extension_Escaper;
use Twig_Loader_Filesystem;
use RuntimeException;

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
    $container->register('DisplayManager.class', '\fajr\rendering\DisplayManager')
              ->addArgument(new sfServiceReference('TwigFactory.class'))
              ->addArgument('%Template.Skin.Default%');

    $skins = FajrConfig::get('Template.Skin.Skins');
    $skinName = FajrConfig::get('Template.Skin.Default');

    if (!isset($skins, $skinName)) {
      throw new RuntimeException("Default skin is not present!");
    }

    $container->setParameter('Template.Skin.Default', $skins[$skinName]);

    $container->register('TwigFactory.class', '\fajr\rendering\TwigFactory')
              ->addArgument('%Twig.Environment.options%')
              ->addArgument('%Twig.Environment.extensions%');

    $container->setParameter('Twig.Environment.extensions',
                              array(new Twig_Extension_Escaper(), new Extension()));

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
