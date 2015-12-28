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
 * Tento súbor obsahuje parser AIS html tabuliek do poľa stringov.
 *
 * @package libfajr
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 * @filesource
 */

/**
 * Trieda, ktorá poparsuje AIS html tabuľku a vyrobí z nej pole
 * jednotlivých riadkov.
 *
 * @package libfajr
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */
class AIS2Table {
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
   * Konštruktor, z html kódu vyrobí dáta.
   *
   * @param array(string) $tableDefinition  názvy stĺpcov
   * @param string        $html             html vygenerované AISom
   */
  public function __construct($tableDefinition, $html)
  {
    $this->definition = $tableDefinition;
    // Ak v tabulke nie su ziadne data, matchAll nic nenajde, ale nemame vyhodit vynimku
    if (trim($html) == '') {
      $this->data = array();
      return;
    }

    $data = matchAll($html, $this->getPattern());
    $this->data = array();
    if ($data !== false) {
      foreach ($data as $row) {
        $this->data[] = removeIntegerIndexesFromArray($row);
      }
    } else {
      throw new Exception("Problém pri parsovaní dát.");
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

  /**
   * Vráti regulárny výraz použitý na matchovanie tabuľky v HTML výstupe z AISu.
   * Na jeho konštrukciu sa používa definícia tabuľky.
   *
   * @return string Regulárny výraz.
   */
  private function getPattern()
  {
    $pattern = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'[^>]*\>';
    foreach ($this->definition as $column) {
      $pattern .= '\<td[^>]*\>(\<div\>){0,1}(?P<'.substr($column, 0, 32).
                  '>[^<]*)(\</div\>){0,1}\</td\>';
    }
    $pattern .= '\</tr\>@';
    return $pattern;
  }
}
