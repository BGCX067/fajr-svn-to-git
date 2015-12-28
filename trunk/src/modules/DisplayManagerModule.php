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
use fajr\config\FajrConfigOptions;
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
  private $config;

  public function __construct(FajrConfig $config) {
    $this->config = $config;
  }

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

    $skins = $this->config->get(FajrConfigOptions::TEMPLATE_SKINS);
    $skinName = $this->config->get(FajrConfigOptions::TEMPLATE_DEFAULT_SKIN);

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
                             $this->config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATES));

    if ($this->config->get(FajrConfigOptions::USE_TEMPLATE_CACHE)) {
      $cache = $this->config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATE_CACHE);
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
