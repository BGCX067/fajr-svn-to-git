<?php
/**
 * Twig factory can create Twig_Environment which can be used to render
 * provided skin.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Environment;
use Twig_Loader_Filesystem;
use fajr\config\SkinConfig;
use fajr\config\FajrConfig;

/**
 * Provides Twig_Environment which can be used to render
 * provided skin.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TwigFactory {
  /** @var array options for Twig_Environment */
  private $twigOptions;
  /** @var array twig extensions */
  private $extensions;


  /**
   * @param array $twigEnvironmentOptions options for Twig_Environment
   * @param array $extensions extensions for Twig_Environment
   */
  public function __construct(array $twigEnvironmentOptions, array $extensions) {
    $this->twigOptions = $twigEnvironmentOptions;
    $this->extensions = $extensions;
  }

  /**
   * Provide Twig_Environment configured for rendering specific skin.
   *
   * @param SkinConfig $skin skin to use.
   *
   * @returns Twig_Environment
   */
  public function provideTwigForSkin(SkinConfig $skin) {
    $paths = $skin->getAllPaths();
    $loader = new Twig_Loader_Filesystem($paths);
    $twig = new Twig_Environment($loader, $this->twigOptions);
    foreach ($this->extensions as $extension) {
      $twig->addExtension($extension);
    }
    return $twig;
  }

}
