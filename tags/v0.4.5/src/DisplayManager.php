<?php
/**
 * Display manager provides a way to render a Response
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;

use Twig_Environment;
use fajr\libfajr\base\Preconditions;

/**
 * Display manager provides a way to render a Response
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class DisplayManager
{
  private $twig;

  /**
   * Construct a DisplayManager using Twig_Environment
   * @param Twig_Environment $twig
   */
  public function __construct(Twig_Environment $twig)
  {
    $this->twig = $twig;
  }

  /**
   * Generate a page content
   * @param Response $response response data to use to generate output
   * @return string Generated output to be sent to the browser
   */
  public function display(Response $response)
  {
    Preconditions::checkNotNull($response->getTemplate(), "Template not set");

    $templateName = 'pages/'.$response->getTemplate().'.xhtml';
    $template = $this->twig->loadTemplate($templateName);

    $output = $template->render($response->getData());

    return $output;
  }
}

?>
