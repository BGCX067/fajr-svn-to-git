<?php
/* {{{
Copyright (c) 2010 Martin Králik

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

/**
 * Tento súbor obsahuje objekt zaobaľujúci tabuľku dát.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr;

/**
 * Trieda zastrešujúca tabuľku dát.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */
class DataTable {
  /**
   * Definícia stĺpcov tabuľky
   * @var array(string)
   */
  private $definition = null;

  /**
   * Samotné dáta poparsované pri konštrukcii objektu.
   * @var array(array(string=>string))
   */
  private $data = null;

  /**
   * Konštruktor, z neindexovanej tabulky
   * vytvori tabulku indexovanu stlpcami z $tableDefinition.
   *
   * @param array(string)        $tableDefinition  názvy stĺpcov
   * @param array(array(string)) $tableData data
   */
  public function __construct($tableDefinition, $tableData)
  {
    $this->definition = $tableDefinition;
    assert(is_array($tableData));
    $this->data = array();
    foreach ($tableData as $key=>$tableRow) {
      $myRow = array();
      $myRow['index'] = $key;
      assert(count($tableDefinition) == count($tableRow));

      foreach($tableRow as $key=>$value) {
        assert(is_numeric($key));
        assert(isset($tableDefinition[$key]));
        $myRow[$tableDefinition[$key]] = $value;
      }

      $this->data[] = $myRow;

    }
  }

  /**
   * Vráti riadky tabuľky.
   *
   * @return array(array(string=>string)) riadky tabuľky
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Vráti definíciu stĺpcov použitú v konštruktore a pri parsovaní.
   *
   * @return array(string) názvy stĺpcov.
   */
  public function getTableDefinition()
  {
    return $this->definition;
  }
}
