<?php
/* {{{
Copyright (c) 2010 Peter Peresini

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

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
