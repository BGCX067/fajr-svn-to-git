<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr;
use fajr\htmlgen\Renderable;
class TabManager implements Renderable {

  private $tabs = array();
  private $active = null;
  private $name = '';
  private $urlParams = null;

  public function __construct($name, array $urlParams) {
    $this->name = $name;
    $this->urlParams = $urlParams;

  }

  public function addTab($name, $title, Renderable $content) {
    if (isset($this->tabs[$name])) {
      throw new Exception('Pokus o predefinovanie existujúceho tabu');
    }
    $this->tabs[$name] = array('name' => $name, 'title' => $title, 'content' => $content);
    // Po pridani prveho tabu do prazdneho TabManagera je tento implicitne aktivny
    if ($this->active === null) {
      $this->setActive($name);
    }
  }

  public function setActive($tabName) {
    if (!isset($this->tabs[$tabName])) {
      throw new Exception('Takýto tab neexistuje!');
    }
    $this->active = $tabName;
  }

  private function getActiveTab() {
    if ($this->active === null) {
      throw new Exception('Nebol nastavený aktívny tab!');
    }
    return $this->tabs[$this->active];
  }

  public function getHtml() {
    $activeTab = $this->getActiveTab();

    $code = '<div class=\'tab_header\'>';
    foreach ($this->tabs as $key => $value) {
      $link = FajrUtils::linkUrl(array_merge($this->urlParams,
            array($this->name => $key)));
      if ($key == $activeTab['name']) $class='tab_selected'; else $class='tab';
      $code .= '<span class=\''.$class.'\'><a href="'.$link.'">'.$value['title']
        .'</a></span>';
    }
    $code .= '</div>';

    try {
      $code .= $activeTab['content']->getHtml();
    } catch (Exception $e) {
      DisplayManager::addException($e);
    }
    return $code;
  }

}

?>
