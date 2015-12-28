<?php
/**
 * Loads configuration from file and provides means to access it
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Config
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\config;

use Exception;
use fajr\libfajr\base\IllegalStateException;
use fajr\libfajr\base\Preconditions;
use fajr\validators\StringValidator;
use fajr\validators\ChoiceValidator;
use fajr\config\ConfigUtils;
use fajr\util\FajrUtils;
use InvalidArgumentException;

/**
 * Loads configuration from file and provides means to access it
 * @package    Fajr
 * @subpackage Config
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class FajrConfigLoader
{
  protected static $config = null;

  /**
   * @returns string absolute path to configuration file
   */
  public static function getConfigurationFileName()
  {
    return FajrUtils::joinPath(FajrUtils::getProjectRootDirectory(), '/config/configuration.php');
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

    self::$config = new FajrConfig($result);
  }

  /**
   * Check whether configuration is ready
   * @returns boolean true iff configuration was successfully loaded
   */
  public static function isConfigured()
  {
    return (self::$config !== null);
  }

  public static function getConfiguration()
  {
    return self::$config;
  }

}

FajrConfigLoader::load();
