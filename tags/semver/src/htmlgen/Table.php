<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Králik <majak47@gmail.com>
 */
namespace fajr\htmlgen;
use fajr\DisplayManager;
use fajr\FajrUtils;
class TableRow implements Renderable
{
  private $data = null;
  private $options = null;
  
  public function __construct(Table $table, array $rowData, $options = null, $isFooter = false) {
    assert($isFooter || isset($rowData['index'])); // row should have index
    $this->data = $rowData;
    $this->options = $options;
    $this->table = $table;
    $this->isFooter = $isFooter;
  }
  
  public function getData() {
    return $this->data;
  }
  
  public function getHtml() {
    $table = $this->table;
    $columns = $table->getColumns();
    $class='';
    if (isset($this->options['class'])) {
      $class=$this->options['class'];
    }
    if (!$this->isFooter) {
      if ($table->GetOption('selected_key') !== null) {
        $sKey = $table->getOption('selected_key');
        if (is_array($sKey)) {
          $selected = true;
          foreach ($sKey as $key=>$value) {
            if ($value != $this->data[$key]) $selected=false;
          }
        } else {
          $selected = ($sKey == $this->data['index']);
        }
        if ($selected) $class='selected';
      }
    }
    
    $row = "<tr class='$class'>\n";
    
    if ($table->newKey) {
      if (is_array($table->newKey)) {
        $params = $table->urlParams;
        foreach ($table->newKey as $key=>$tableCol) {
          $params[$key] = $this->data[$tableCol];
        }
        $link = FajrUtils::linkUrl($params);
      } else {
        $link = FajrUtils::linkUrl(array_merge($table->urlParams,
              array($table->newKey => $this->data['index'])));
      }
    }
    
    $colno = 0;
    foreach ($columns as $key => $column)
    {
      $real_key = substr($key, 0, 32);
      if (isset($this->data[$real_key])) {
        $cell_value = $this->data[$real_key];
      }
      else {
        $cell_value = '';
      }
      if ($cell_value=="") $cell_value="&nbsp;";
        
      $cell = '    <td>';
      if ($table->newKey && $colno==0) {
        $cell .= '<a href="'.$link.'">'.$cell_value."</a>";
      } else {
        $cell .= $cell_value;
      }
      $cell .= "</td>\n";
      
      $row .= $cell;
      $colno++;
    }
    $row .= "</tr>\n";
    
    return $row;
  }
  
}


/**
 * Trieda na vygenerovanie tabulky z jej definície a vstupného HTML.
 *
 * @author majak
 */
class Table implements Renderable
{
  protected $definition = null;
  protected $data = array();
  public $newKey = null;
  public $urlParams = array();
  protected $options = null;

/**
 * Konštruktor.
 * Nastaví atribúty a zo vstupného HTML dá tabuľku do poľa.
 * @param array $definition Definícia tabuľky.
 * @param string $html HTML z AISu.
 * @param string|null $newKey Názov nového parametru v url, ktorého hodnota bude závisieť od riadku tabuľky.
 * @param array $urlParams Zvyšné už nastavené parametre pre url.
 */
  public function  __construct(array $definition, $newKey = null, array $urlParams = array())
  {
    $this->definition = $definition;
    $this->newKey = $newKey;
    $this->urlParams = $urlParams;
    $this->footer = null;
  }
  
  public function addRows($data) {
    foreach ($data as $row) {
        $this->addRow($row, null);
    }
  }
  
  public function addRow($rowData, $rowOptions) {
    $this->data[] = new TableRow($this, $rowData, $rowOptions);
  }

  public function addFooter($rowData, $rowOptions) {
    $this->footer[] = new TableRow($this, $rowData, $rowOptions, true);
  }

  public function setNewKey($newKey)
  {
    $this->newKey = $newKey;
  }

  public function setUrlParams($urlParams)
  {
    $this->urlParams = $urlParams;
  }
  
  public function setOptions($options)
  {
    $this->options = $options;
  }
  
  public function setOption($name, $value) {
    $this->options[$name] = $value; 
  }
  
  public function getOption($name) {
    if (isset($this->options[$name])) {
      return $this->options[$name];
    }
    return null;
  }
  
  public function getColumns() {
    $data=array();
    foreach ($this->definition as $key => $value) {
      if (!$value['visible']) continue; // skip invisible cells
      $colNum = 0;
      if (isset($value['col'])) $colNum=$value['col'];
      $data[] = array($colNum, $key);
    }
    sort($data);
    $columns=array();
    foreach ($data as $row) {
      $columns[$row[1]] = $this->definition[$row[1]];
    }
    return $columns;
  }

  /**
   * Pomocou nastavených atribútov vygeneruje tabuľku v HTML formáte aj s linkami.
   * @return string Vygenerovaná tabuľka v HTML formáte.
   */
  public function getHtml()
  {
    $id = DisplayManager::getUniqueHTMLId('table');
    
    $table = "\n<!-- ******* Table ****** -->\n";
    
    if (!is_array($this->data) || empty($this->data[0])) {
      $table .= '<font color="red"> Dáta pre túto tabuľku neboli nájdené.</font><hr class="space" />';
      return $table;
    }
    
    $table .= "<table id=\"".$id."\"class='colstyle-sorting'>\n<thead>\n<tr>\n";
    $columns = $this->getColumns();
    
    foreach ($columns as $key=>$value) {
      $table .= '    <th class="sortable">'.$value['title']."</th>\n";
    }
    
    $table .= "</tr>\n";
    $table .= "\n</thead>\n";
    // Tu by mal byt TFOOT, vid nizsie
    $table .= "<tbody>\n";
    foreach ($this->data as $row)
    {
      $table .= $row->getHtml();
    }
    $table .= "</tbody>";
    // FIXME: Tento TFOOT by mal byt podla specifikacie pred TBODY,
    // ale ked je tu, je mozne css-kom vypnut opakovanie
    // footer riadkov pri tlaci (inak by boli na vrchu tabulky).
    // Ak chceme mat vystup 100% validny, treba upravit sorter skript,
    // aby bral do uvahy nejaky class a zmenit tento TFOOT na TBODY.
    if ($this->footer !== null) {
      $table .= '<tfoot>';
      foreach ($this->footer as $row) {
        $table .= $row->getHtml();
      }
      $table .= "</tfoot>\n";
    }
    $table .= "</table>\n";
    return $table;
  }

}
?>
