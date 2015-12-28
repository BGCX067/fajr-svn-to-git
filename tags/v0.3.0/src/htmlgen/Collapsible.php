<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Obsahuje generator html kodu, ktory sa da skryt javascriptom
 *
 * @package Fajr
 * @subpackage Html
 * @author Martin Kralik <majak47@gmail.com>
 * @author Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr\htmlgen;
use fajr\DisplayManager;
/**
 * Trieda, ktora vygeneruje okolo daneho Renderable taky HTML kod, aby
 * ho bolo mozne javascriptom schovat
 * @package Fajr
 * @subpackage Html
 * @author Martin Kralik <majak47@gmail.com>
 * @author Martin Sucha <anty.sk@gmail.com>
 */
class Collapsible implements Renderable
{

  /** 
   * titulok, ktory je zobrazeny vzdy, bez ohladu na
   * to ci je element skryty alebo nie.
   * Pozor, titulok moze obsahovat html kod
   * a preto musi byt bezpecny!
   */
  protected $title = null;

  /** obsah, ktory chceme vediet skryvat */
  protected $content = null;

  /** je element aktualne skryty? */
  protected $collapsed = false;

  /**
   * Konstruktor
   * @param string      $title      Titulok schovavanej oblasti
   * @param Renderable  $content    Obsah schovavanej oblasti
   * @param bool        $collapsed  Je oblast schovana?
   */
  function __construct(Renderable $title, Renderable $content, $collapsed = false)
  {
    $this->setTitle($title);
    $this->setContent($content);
    $this->setCollapsed($collapsed);
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function setTitle(Renderable $title)
  {
    $this->title = $title;
  }

  public function getCollapsed()
  {
    return $this->collapsed;
  }

  public function setCollapsed($collapsed)
  {
    $this->collapsed = $collapsed;
  }

  public function getContent()
  {
    return $this->content;
  }

  public function setContent(Renderable $content)
  {
    $this->content = $content;
  }

  public function getHtml()
  {
    $id = DisplayManager::getUniqueHTMLId('collapsible');

    $html = '<div class="collapsible" id="'.$id.'"'."\n";
    $html .= '<div class="collapsibleheader togglevisibility" onclick=\'toggleVisibility("'.$id.'");\' >';
    $html .= $this->title->getHtml().'</div>'."\n";
    $html .= '<div class="collapsiblecontent">';
    $html .= $this->content->getHtml();
    $html .= '</div>';
    $html .= "</div>\n\n\n";
    if ($this->collapsed) {
      $html .= '<script type="text/javascript"> toggleVisibility("';
      $html .= $id."\") </script>\n";
    }

    return $html;
  }
}
