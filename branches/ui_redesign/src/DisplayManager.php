<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Králik <majak47@gmail.com>
 */
namespace fajr;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Escaper;
use fajr\libfajr\base\Preconditions;
use fajr\rendering\Extension;

class DisplayManager
{

  /**
   * Generate a page content
   * @param Response $response response data to use to generate output
   * @return string Generated output to be sent to the browser
   */
  public function display(Response $response)
  {
    if ($response->getTemplate() === null) {
      return;
    }

    $templateDir = FajrConfig::getDirectory('Template.Directory');
    $loader = new Twig_Loader_Filesystem($templateDir);
    $twig = new Twig_Environment($loader, array(
                        'base_template_class' => '\fajr\rendering\Template',
                        'strict_variables' => true));
    $twig->addExtension(new Twig_Extension_Escaper());
    // Register fajr's rendering extension
    $twig->addExtension(new Extension());

    $templateName = 'pages/'.$response->getTemplate().'.xhtml';
    $template = $twig->loadTemplate($templateName);

    $output = $template->render($response->getData());
    
    return $output;
  }
}

?>
