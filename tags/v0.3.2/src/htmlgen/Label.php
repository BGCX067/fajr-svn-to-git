<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Jednoducha implementacia Renderable, ktora vracia dany text ako svoju HTML
 * reprezentaciu
 *
 * @author Martin Sucha <anty.sk@gmail.com>
 */

namespace fajr\htmlgen;

class Label implements Renderable {

  protected $text;

  function __construct($text) {
    $this->setText($text);
  }

  public function getText() {
    return $this->text;
  }

  public function setText($text) {
    $this->text = $text;
  }

  public function getHtml() {
    return $this->text;
  }


}
