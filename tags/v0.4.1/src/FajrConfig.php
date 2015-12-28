<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;
class FajrConfig
{
  protected static $config = null;

  /**
   * Default values for configuration options
   *
   * @var array key=>value
   * @see configuration.example.php for more information
   */
  protected static $defaultOptions = array(
    'Debug.Banner'=>false,
    'Debug.Trace'=>false,
    'Debug.Path'=>false,
    'Debug.Rewrite'=>false,
    'Debug.Exception.ShowStacktrace'=>false,
    'Path.Temporary'=>'./temp',
    'Path.Temporary.Cookies'=>'./cookies',
    'Path.Temporary.Sessions'=>'./sessions',
    'AIS2.ServerList' => array(),
    'SSL.CertificatesDir'=>null,
    'Connection.UserAgent'=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7',
    'Template.Directory'=>'./templates/fajr',
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
    if (self::isConfigured()) {
      return;
    }

    $result = (include '../config/configuration.php');
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
    if (!isset(self::$config[$key])) {
      return null;
    }
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
   * @returns string absolute path for the directory specified in configuration
   *                 or null if this option was not specified and does not have
   *                 a default value
   * @see FajrConfig::$defaultOptions
   * @see FajrConfig::$directoriesRelativeTo
   * @see configuration.example.php
   */
  public static function getDirectory($key)
  {
    $dir = self::get($key);
    if ($dir === null) {
      return null;
    }
    if (FajrUtils::isAbsolutePath($dir)) {
      return $dir;
    }
    // default resolve relative
    $relativeTo = FajrUtils::joinPath(dirname(__FILE__), '..');
    if (!empty(self::$directoriesRelativeTo[$key])) {
      $relativeTo = self::getDirectory(self::$directoriesRelativeTo[$key]);
    }
    return FajrUtils::joinPath($relativeTo, $dir);
  }
}

FajrConfig::load();
