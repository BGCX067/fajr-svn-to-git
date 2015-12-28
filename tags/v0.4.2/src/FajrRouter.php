<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace fajr;

/**
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

class FajrRouter
{
  /**
   * Vyrobi cestu z parametrov dotazu. Z params odstrani vsetky kluce, ktore
   * sa nemaju objavit v query stringu
   *
   * @param array $params asociativne pole parametrov
   * @returns string vysledna cesta
   */
  public static function paramsToPath(array &$params)
  {
    $path = array();

    if (!empty($params['action'])) {
      $path[] = $params['action'];
      unset($params['action']);
    }

    return implode('/', $path);
  }

  /**
   * Inverzna funkcia k paramsToPath, vrati vsetky parametre, ktore su
   * zakodovane v ceste
   *
   * @param string $path
   * @returns array asociativne pole parametrov
   */
  public static function pathToParams($pathString)
  {
    $params = array();

    if (strlen($pathString)==0) {
      return $params;
    }

    $path = explode('/',$pathString);
    $n = count($path);

    if ($n > 0) {
      $params['action'] = $path[0];
    }
    
    return $params;
  }

}
