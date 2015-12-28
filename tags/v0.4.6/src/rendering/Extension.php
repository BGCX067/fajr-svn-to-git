<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje template Twig extension pre Fajr
 *
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Extension;
use fajr\rendering\tags\UniqueTokenParser;

/**
 * Provides Fajr customizations for the Twig templating engine
 *
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Extension extends Twig_Extension
{
  /**
   * Return an extension name
   * @returns string extension name
   */
  public function getName()
  {
    return 'fajr';
  }

  /**
   * Returns the token parser instances to add to the existing list.
   *
   * @returns array An array of Twig_TokenParser instances
   */
  public function getTokenParsers()
  {
    return array(new UniqueTokenParser(),
                 new tags\URLTokenParser(),
                );
  }


}
