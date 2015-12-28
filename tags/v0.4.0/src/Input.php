<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Description of Input
 *
 * @package    Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;
require_once 'Validator.php';
 
use Exception;

class Input
{
  protected static $inputParameters = array();
  protected static $_get = array();
  protected static $_post = array();
  
  protected static $allowedParamters = array(
    '_get' => array(
      'studium' => 'int',
      'list' => 'int',
      'predmet' => 'int',
      'termin' => 'int',
      'action' => 'string',
      'loginType' => 'string',
    ),
    '_post' => array(
      'prihlasPredmetIndex' => 'int',
      'prihlasTerminIndex' => 'int',
      'odhlasIndex' => 'int',
      'hash' => 'string',
      'action' => 'string',
      'login' => 'string',
      'krbpwd' => 'string',
      'cosignCookie' => 'string',
      'loginType' => 'string',
    ),
  );
  
  protected static $conditions = array(
    'int' => array(
      'cond' => 'isInteger',
      'options' => array(),
      'message' => 'Vstupný parameter "%%NAME%%" musí byť typu integer.',
    ),
    'string' => array(
      'cond' => 'isString',
      'options' => array('minLength' => 1),
      'message' => 'Vstupný parameter "%%NAME%%" nesmie byť prázdny.',
    ),
  );

  public static function prepare()
  {
    if (FajrConfig::get('URL.Path')) {
      $_get = array_merge(FajrRouter::pathToParams(FajrUtils::pathInfo()),$_GET);
    }
    else {
      $_get = $_GET;
    }
    $_post = $_POST;
  
    // podla pola definujeceho vstupne parametre overim ich platnost
    foreach (self::$allowedParamters as $input => $params) {
      foreach ($params as $name => $type) {
        if (isset(${$input}[$name])) {
          $checker = self::$conditions[$type]['cond'];
          if (!Validator::$checker(${$input}[$name], self::$conditions[$type]['options'])) {
            throw new Exception(str_replace('%%NAME%%', $name, self::$conditions[$type]['message']));
          }
          self::$inputParameters[$name] = ${$input}[$name];
          self::${$input}[$name] = ${$input}[$name];
        }
      }
    }
    
    // budeme pouzivat uz len Input
    unset($_GET);
    unset($_POST);
  }

  public static function get($key = null)
  {
    if ($key === null) {
      return self::$inputParameters;
    }
    if (!isset(self::$inputParameters[$key])) {
      return null;
    }
    else {
      return self::$inputParameters[$key];
    }
  }
  
  public static function set($key, $value)
  {
    self::$inputParameters[$key] = $value;
  }
  
  public static function getUrlParams()
  {
    return self::$_get;
  }
}
?>
