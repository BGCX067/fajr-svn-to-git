<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

require_once 'FajrRouter.php';
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\login\AIS2LoginImpl;
use fajr\libfajr\AIS2Session;
use fajr\libfajr\util\StrUtil;

class FajrUtils
{
  public static function login(Trace $trace, Login $login, AIS2ServerConnection $connection)
  {
    $trace->tlog("Creating AIS2Session");
    $session = new AIS2Session($login);

    $trace->tlog("logging in");
    if (!$login->login($connection)) {
      return false;
    }
    $trace->tlog("logged in correctly.");

    $_SESSION['AISSession'] = $session;
    self::redirect();
    return true;
  }

  /**
   * Odhlási z Cosignu a zmaže lokálne cookies.
   */
  public static function logout(AIS2ServerConnection $connection)
  {
    if (!isset($_SESSION['AISSession'])) {
      return false;
    }
    if ($_SESSION['AISSession']->getLogin()->logout($connection)) {
      unset($_SESSION['AISSession']);
      self::dropSession();
    }
  }

  /**
   * Ensure current session is disposed of and new clean session is created
   */
  public static function dropSession()
  {
    session_regenerate_id(true);
  }

  public static function isLoggedIn(AIS2ServerConnection $connection)
  {
    if (!isset($_SESSION['AISSession'])) {
      return false;
    }
    $login = $_SESSION['AISSession']->getLogin();
    return $login->isLoggedIn($connection) ||
           $login->ais2Relogin($connection);
  }

  public static function redirect($newParams = array(), $file='fajr.php')
  {
    header('Location: ' . self::buildUrl($newParams, $file));
    exit();
  }

  /**
   * Returns a cookie file path for current session.
   *
   * Cookie file name is not the same as session_id() so that if one
   * configures the same path for cookie and session directories,
   * the filenames do not clash.
   *
   * @returns string file path to use to store cookies into.
   */
  public static function getCookieFile()
  {
    return self::joinPath(FajrConfig::getDirectory('Path.Temporary.Cookies'), 'cookie_'.session_id());
  }

  public static function buildUrl($params, $file=null)
  {
    if ($file === null) {
      if (!empty($params['_file'])) {
        $file = $params['_file'];
        unset($params['_file']);
      }
      else {
        $file = 'fajr.php';
      }
    }

    $path = '';
    if (FajrConfig::get('URL.Path')) {
      $path = FajrRouter::paramsToPath($params);
    }
    $query = http_build_query($params);
    if (strlen($query) > 0) {
      $query = '?' . $query;
    }

    $base = '';

    if (!FajrConfig::get('URL.Rewrite') || $file != 'fajr.php' || $path == '' || $path == '/') {
      $base = $file;
      if (strlen($path) > 0) {
        $base .= '/';
      }
    }

    return self::basePath() . $base . $path . $query;
  }

  public static function pathInfo()
  {
    if (!isset($_SERVER['PATH_INFO'])) {
      return '';
    }
    $path = $_SERVER['PATH_INFO'];
    if (substr_compare($path, '/', 0, 1) == 0) {
      $path = substr($path, 1);
    }
    return $path;
  }

  /**
   * @returns bool whether the current connection is secured
   */
  public static function isHTTPS()
  {
    return ((isset($_SERVER['HTTPS'])) &&
           ($_SERVER['HTTPS'] !== 'off') &&
           ($_SERVER['HTTPS'])) ||
           ((isset($_SERVER['SERVER_PORT'])) && $_SERVER['SERVER_PORT'] == '443');
  }

  public static function basePath()
  {
    $url = '';
    if (self::isHTTPS()) {
      $url = 'https://';
    } else {
      $url = 'http://';
    }
    $url .= $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['SERVER_PORT'])) {
      $port = $_SERVER['SERVER_PORT'];
      if ($port != '80' && $port != '443') {
        $url .= ':' . $port;
      }
    }

    $dname = dirname($_SERVER['SCRIPT_NAME']);

    if ($dname !== '/') {
      $dname .= '/';
    }

    $url .= $dname;

    return $url;
  }

  /**
   * Determines whether given $path is an absolute path or not.
   * A path is absolute if it starts with filesystem root definition
   * (i.e. / on unix like systems and C:\ or \\ on Windows)
   *
   * @param string $path true if the path is relative
   */
  public static function isAbsolutePath($path)
  {
    // check for unix-like /
    if (StrUtil::startsWith($path, '/')) {
      return true;
    }

    // check for Windows UNC path
    if (StrUtil::startsWith($path, '\\\\')) {
      return true;
    }

    // check for Windows drive letter
    if (preg_match('/^[A-Z]:/', $path)) {
      return true;
    }

    return false;
  }

  /**
   * Joins two or more path components.
   * If joining more than two path components, the result is
   * the same as calling two-argument joinPath successively.
   * Moreover, this function is associative, i.e.
   * joinPath('a','b','c') has the same effect as
   * joinPath(joinPath('a', 'b'), 'c') or joinPath('a', joinPath('b', 'c'))
   *
   * Path components of zero length are ignored
   *
   * @param string $a first path component
   * @param string $b second path component
   * @param string ... any other path components to join
   * @returns string all the paths joined using a directory separator
   */
  public static function joinPath($a, $b)
  {
    $args = func_get_args();
    $num_args = count($args);
    $shouldAddDS = true;

    // start with $a, omit trailing directory separator
    if ($a == DIRECTORY_SEPARATOR || $a == '') {
      $shouldAddDS = false;
      $path = $a;
    }
    else if (StrUtil::endsWith($a, DIRECTORY_SEPARATOR)) {
      $path = substr($a, 0, strlen($a) - 1);
    }
    else {
      $path = $a;
    }

    // add other components
    for ($i = 1; $i < $num_args; $i++) {
      $part = $args[$i];
      // DIRECTORY_SEPARATOR or empty string is a special case
      if ($part == DIRECTORY_SEPARATOR) {
        continue;
      }

      // first extract range of part without leading or trailing DS
      if (StrUtil::startsWith($part, DIRECTORY_SEPARATOR)) {
        $start = 1;
      } else {
        $start = 0;
      }
      if (StrUtil::endsWith($part, DIRECTORY_SEPARATOR)) {
        $end = strlen($part) - 1;
      } else {
        $end = strlen($part);
      }

      // append a path component
      if ($shouldAddDS) {
        $path .= DIRECTORY_SEPARATOR;
      }
      $shouldAddDS = true;
      $path .= substr($part, $start, $end);
    }

    return $path;
  }

  /**
   * Format plural according to Slovak language rules
   * @param int    $number number to decide on and pass as argument to sprintf
   * @param string $zero   sprintf format string if $number is 0 (zero)
   * @param string $one    sprintf format string if $number is 1 (one)
   * @param string $few    sprintf format string if $number is between 2 and 4 (inclusive)
   * @param string $many   sprintf format string for other $number values
   */
  // TODO(ppershing): @deprecate, use name sprintfPlural() or something that informs about using
  // printf formatting characters.
  public static function formatPlural($number, $zero, $one, $few, $many)
  {
    if ($number == 0) {
      return sprintf($zero, $number);
    }
    else if ($number == 1) {
      return sprintf($one, $number);
    }
    else if ($number >= 2 && $number <= 4) {
      return sprintf($few, $number);
    }
    else {
      return sprintf($many, $number);
    }
  }

}
