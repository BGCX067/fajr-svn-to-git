<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt zaobaľujúci tabuľku dát.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\data_manipulation\SimpleDataTable;

/**
 * Trieda zastrešujúca tabuľku dát.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class DataTableImpl implements SimpleDataTable
{
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
    foreach ($tableData as $rowKey=>$tableRow) {
      $myRow = array();
      $myRow['index'] = $rowKey;
      assert(count($tableDefinition) == count($tableRow));

      foreach($tableRow as $key=>$value) {
        assert(is_numeric($key));
        assert(isset($tableDefinition[$key]));
        $myRow[$tableDefinition[$key]] = $value;
      }

      $this->data[$rowKey] = $myRow;

    }
  }

  /**
   * Vráti riadky tabuľky.
   *
   * @returns array(array(string=>string)) riadky tabuľky
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Vráti definíciu stĺpcov použitú v konštruktore a pri parsovaní.
   *
   * @returns array(string) názvy stĺpcov.
   */
  public function getTableDefinition()
  {
    return $this->definition;
  }
}
