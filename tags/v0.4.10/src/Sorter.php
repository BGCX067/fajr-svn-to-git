<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

class SorterHelper
{
  private $columns = null;

  public function __construct($columns)
  {
    if (is_array($columns)) {
      $this->columns = $columns;
    } else {
      $this->columns = array("$columns"=>"1");
    }
  }

  public function compare(&$a, &$b)
  {
    foreach ($this->columns as $field=>$dir) {
      $t = strcmp($a[$field], $b[$field]);
      if ($t!=0) {
        return $t*$dir;
      }
    }
    return 0;
  }
}

// TODO(ppershing): prerobit zo static na runtime + pridat testy
class Sorter
{
  public static function sort($data, $columns)
  {
    $helper = new SorterHelper($columns);
    usort($data, array($helper,"compare"));
    return $data;
  }

  public static function reverse($data)
  {
    return array_reverse($data);
  }
}
