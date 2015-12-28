<?php
/**
 * Display manager provides a way to render a Response
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Environment;
use fajr\libfajr\base\Preconditions;
use fajr\config\SkinConfig;
use fajr\Response;

/**
 * Display manager provides a way to render a Response
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class DisplayManager
{
  /** @var TwigFactory */
  private $twig;
  /** @var SkinConfig */
  private $defaultSkin;

  /**
   * Construct a DisplayManager using Twig_Environment
   * @param Twig_Environment $twig
   * @param SkinConfig skin for which we are going to render templates.
   */
  public function __construct(TwigFactory $twigFactory, SkinConfig $defaultSkin)
  {
    $this->twigFactory = $twigFactory;
    $this->defaultSkin = $defaultSkin;
  }

  /**
   * Generate a page content
   *
   * @param Response $response response data to use to generate output
   *
   * @returns string Generated output to be sent to the browser
   */
  public function display(Response $response)
  {
    Preconditions::checkNotNull($response->getTemplate(), "Template not set");
    if ($response->getSkin()) {
      $skin = $response->getSkin();
    } else {
      $skin = $this->defaultSkin;
    }
    $twig = $this->twigFactory->provideTwigForSkin($skin);

    $templateName = 'pages/' . $response->getTemplate() . '.xhtml';
    $template = $twig->loadTemplate($templateName);

    $output = $template->render($response->getData());

    return $output;
  }
}

?>
