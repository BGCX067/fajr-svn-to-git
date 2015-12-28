<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace fajr\htmlgen;
/**
 * Jednoducha implementacia Renderable, ktora vracia spojenie html
 * reprezentacii clenskych objektov
 *
 * @author Martin Sucha <anty.sk@gmail.com>
 */

class Container implements Renderable {

  protected $children = null;

  public function __construct() {
    $this->children = array();
  }

  public function addChild(Renderable $child) {
    $this->children[] = $child;
  }

  public function getHtml() {
    $html = '';
    foreach ($this->children as $child) {
      $html .= $child->getHtml();
    }
    return $html;
  }



}
