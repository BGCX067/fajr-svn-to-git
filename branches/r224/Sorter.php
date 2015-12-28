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

class SorterHelper {
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
      if ($t!=0) return $t*$dir;
    }
    return 0;
  }
}

class Sorter {
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
