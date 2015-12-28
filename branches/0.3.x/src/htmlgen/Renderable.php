<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Interface pre vsetky objekty generujuce html kod
 *
 * @package Fajr
 * @subpackage Html
 * @author Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\htmlgen;
/**
 * Indikuje, že tento objekt vie vygenerovať svoj obsah ako HTML
 *
 * @package Fajr
 * @subpackage Html
 * @author Martin Sucha <anty.sk@gmail.com>
 */
interface Renderable {

  /**
   * Vráti obsah tohto objektu ako html reťazec
   * @returns string html kód
   */
  public function getHtml();
}
