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

require_once 'Validator.php';
 
/**
 * Description of Input
 *
 * @author majak
 */
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
      'tab' => 'string',
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
    ),
  );
  
  protected static $conditions = array(
    'int' => array(
      'cond' => 'number',
      'options' => array(),
      'message' => 'Vstupný parameter "%%NAME%%" musí byť typu integer.',
    ),
    'string' => array(
      'cond' => 'string',
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
    foreach (self::$allowedParamters as $input => $params)
    {
      foreach ($params as $name => $type) if (isset(${$input}[$name]))
      {
        $checker = self::$conditions[$type]['cond'];
        if (!Validator::$checker(${$input}[$name], self::$conditions[$type]['options']))
          throw new Exception(str_replace('%%NAME%%', $name, self::$conditions[$type]['message']));
        self::$inputParameters[$name] = ${$input}[$name];
        self::${$input}[$name] = ${$input}[$name];
      }
    }
    
    // specialne vynimky
    if (isset($_get['logout']))
    {
      self::$inputParameters['logout'] = true;
      //self::$_GET['logout'] = true; FIXME: Majak, co tu robilo toto?
      //Pravdepodobne to chceme umazat.
    }
    
    // budeme pouzivat uz len Input
    unset($_GET);
    unset($_POST);
  }

  public static function get($key = null)
  {
    if ($key === null) return self::$inputParameters;
    if (!isset(self::$inputParameters[$key]))
    {
      return null;
    }
    else
    {
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
