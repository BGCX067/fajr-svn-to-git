<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use Exception;
use fajr\validators\StringValidator;
use fajr\validators\ChoiceValidator;
use fajr\util\ConfigUtils;
use fajr\util\FajrUtils;
use InvalidArgumentException;

/**
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class FajrConfig
{
  protected static $config = null;

  protected static $parameterDescription = null;

  /**
   * Return description of configuration parameters
   *
   * This function caches its result so that it may be called
   * multiple times without performance overhead
   *
   * @returns array key=>
   *                 array('defaultValue'=>value, // if not present,
   *                                              // the param is required
   *                       'relativeTo'=>path, // for directories
   *                       'validator'=>validator // name of validator to use)
   * @see configuration.example.php for more information
   */
  protected static function getParameterDescription()
  {
    if (self::$parameterDescription !== null) {
      return self::$parameterDescription;
    }

    $booleanValidator = new ChoiceValidator(array(true, false));
    $stringValidator = new StringValidator();
    $pathValidator = new StringValidator();

    self::$parameterDescription = array(
      'GoogleAnalytics.Account' =>
        array('defaultValue'=>null),

      'Debug.Banner' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'Debug.Trace' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'Debug.Trace.File' =>
        array('defaultValue' => null,
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      'Debug.Exception.ShowStacktrace' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'URL.Path' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'URL.Rewrite' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'Path.Temporary' =>
        array('defaultValue' => './temp',
              'validator' => $pathValidator),

      'Path.Temporary.Cookies' =>
        array('defaultValue' => './cookies',
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      'Path.Temporary.Sessions' =>
        array('defaultValue' => './sessions',
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),

      'AIS2.ServerList' =>
        array(),

      'AIS2.DefaultServer' =>
        array('validator' => $stringValidator),

      'SSL.CertificatesDir' =>
        array('defaultValue' => null),

      'SSL.Require' =>
        array('defaultValue' => true,
              'validator' => $booleanValidator),

      'Connection.UserAgent' =>
        array('defaultValue' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7',
              'validator' => $stringValidator),

      'Template.Directory' =>
        array('defaultValue' => './templates/fajr',
              'validator' => $pathValidator),

      'Template.Cache' =>
        array('defaultValue' => false,
              'validator' => $booleanValidator),

      'Template.Cache.Path' =>
        array('defaultValue' => './twig_cache',
              'relativeTo' => 'Path.Temporary',
              'validator' => $pathValidator),
    );
    return self::$parameterDescription;
  }

  /**
   * @returns string absolute path to configuration file
   */
  public static function getConfigurationFileName()
  {
    return FajrUtils::joinPath(__DIR__, '../config/configuration.php');
  }

  /**
   * Load configuration file, if it was not loaded previously.
   *
   * This means that second and subsequent calls attempt to load
   * the configuration again only if previous attempts have failed.
   *
   * Otherwise, cached configuration data is used.
   *
   * If the loading fails, isConfigured() will return false.
   *
   * @return void
   */
  public static function load()
  {
    if (self::isConfigured()) {
      return;
    }

    $parameters = self::getParameterDescription();

    $configurationFileName = self::getConfigurationFileName();

    if (!file_exists($configurationFileName)) {
      // Leave fajr unconfigured, index.php will then show nice error message
      // to the user
      return;
    }

    // Don't suppress errors so parse errors are reported
    // TODO(anty): use yaml for configuration
    $result = (include $configurationFileName);

    if (!is_array($result)) {
      throw new Exception('Konfiguračný súbor nevrátil pole');
    }

    $config = ConfigUtils::parseAndValidateConfiguration($parameters, $result);
    foreach ($config['AIS2.ServerList'] as $key => $server) {
      if ($key !== $server->getServerName()) {
        throw new Exception("Nesedí meno servera v konfiguracii AIS2.ServerList");
      }
    }
    self::$config = $config;
  }

  public static function isConfigured()
  {
    return (self::$config !== null);
  }

  public static function get($key)
  {
    // Note: isset() returns false if the item value is null
    if (!array_key_exists($key, self::$config)) {
      throw new InvalidArgumentException('Unknown configuration parameter: ' .
                                         $key);
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

    $parameters = self::getParameterDescription();

    assert(array_key_exists($key, $parameters));

    $param = $parameters[$key];

    if (array_key_exists('relativeTo', $param)) {
      $relativeTo = self::getDirectory($param['relativeTo']);
    }
    return FajrUtils::joinPath($relativeTo, $dir);
  }
}

FajrConfig::load();
