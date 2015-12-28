<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje template subclass Fajru
 *
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Template;

/**
 * Provides Fajr template customizations for the Twig templating engine
 * 
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
abstract class Template extends Twig_Template
{

  protected $nextUniqueId = 1;

  /**
   * Return next unique id and increment the counter
   * @return int unique id
   */
  public function getNextUniqueId()
  {
    return $this->nextUniqueId++;
  }

}