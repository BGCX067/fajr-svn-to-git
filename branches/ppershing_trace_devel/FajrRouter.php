<?php
/* {{{
Copyright (c) 2010 Martin Sucha

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

class FajrRouter
{

  /**
   * Vyrobi cestu z parametrov dotazu. Z params odstrani vsetky kluce, ktore
   * sa nemaju objavit v query stringu
   * @param array $params asociativne pole parametrov
   * @return string vysledna cesta
   */
  public static function paramsToPath(array &$params)
  {

    $path = array();

    if (isset($params['logout'])) {
      $path[] = 'logout';
      unset($params['logout']);
    }
    else if (isset($params['studium'])) {
      $path[] = $params['studium'];
      unset($params['studium']);

      if (isset($params['list'])) {
        $path[] = $params['list'];
        unset($params['list']);

        if (isset($params['tab'])) {
          $path[] = $params['tab'];
          unset($params['tab']);
        }
      }
    }

    return implode('/', $path);
  }

  /**
   * Inverzna funkcia k paramsToPath, vrati vsetky parametre, ktore su
   * zakodovane v ceste
   * @param string $path
   * @return array asociativne pole parametrov
   */
  public static function pathToParams($pathString)
  {
    $params = array();

    if (strlen($pathString)==0) return $params;

    $path = explode('/',$pathString);
    $n = count($path);

    if ($n>0) {
      if ($path[0] == 'logout') {
        $params['logout']=true;
        return $params;
      }

      $params['studium'] = $path[0];
    }
    if ($n>1) {
      $params['list'] = $path[1];
    }
    if ($n>2) {
      $params['tab'] = $path[2];
    }

    return $params;
  }

}
