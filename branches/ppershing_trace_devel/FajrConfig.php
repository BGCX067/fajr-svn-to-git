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

class FajrConfig
{

  protected static $config = null;

  /**
   * Default values for configuration options
   * @var array key=>value
   * @see configuration.example.php for more information
   */
  protected static $defaultOptions = array(
    'Debug.Trace'=>false,
    'Debug.Path'=>false,
    'Debug.Rewrite'=>false,
    'Debug.Exception.ShowStacktrace'=>false,
    'Path.Temporary'=>'./temp',
    'Path.Temporary.Cookies'=>'./cookies',
    'Path.Temporary.Sessions'=>'./sessions',
  );

  /**
   * Specified to which directory a given configuration option
   * should be relative. It maps option names to option names.
   * 'A'=>'B' means, that option A should be resolved relative to
   * directory stored in option B. If not specified or null,
   * directories are resolved relative to the project root directory.
   */
  protected static $directoriesRelativeTo = array(
    'Path.Temporary.Cookies'=>'Path.Temporary',
    'Path.Temporary.Sessions'=>'Path.Temporary',
  );

  public static function load()
  {
    if (self::isConfigured()) return;

    @$result = (include 'configuration.php');
    if ($result !== false && is_array($result)) {
      self::$config = array_merge(self::$defaultOptions, $result);
    }
  }

  public static function isConfigured()
  {
    return (self::$config !== null);
  }

  public static function get($key)
  {
    if (!isset(self::$config[$key])) return null;
    return self::$config[$key];
  }

  /**
   * Get a directory configuration path.
   *
   * If a relative path is given in configuration, it is resolved
   * relative to the specified directory or project root directory
   * if no directory was specified
   *
   * @param string $key
   * @return string absolute path for the directory specified in configuration
   *                or null if this option was not specified and does not have
   *                a default value
   * @see FajrConfig::$defaultOptions
   * @see FajrConfig::$directoriesRelativeTo
   * @see configuration.example.php
   */
  public static function getDirectory($key)
  {
    $dir = self::get($key);
    if ($dir === null) return null;
    if (FajrUtils::isAbsolutePath($dir)) {
      return $dir;
    }
    // default resolve relative
    $relativeTo = dirname(__FILE__);
    if (!empty(self::$directoriesRelativeTo[$key])) {
      $relativeTo = self::getDirectory(self::$directoriesRelativeTo[$key]);
    }
    return FajrUtils::joinPath($relativeTo, $dir);
  }
}

FajrConfig::load();
