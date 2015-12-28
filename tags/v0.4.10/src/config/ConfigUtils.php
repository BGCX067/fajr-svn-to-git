<?php
/**
 * Contains utilities for various config holders.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

namespace fajr\config;

use Exception;

/**
 * Utility function for classes holding configurations.
 *
 * @package    Fajr
 * @subpackage Util
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ConfigUtils
{
  /**
   * Takes a description of configuration parameters as an array and
   * returns parsed and validated configuration.
   * Example of description:
   * <code>
   * array(
   *  'ServerName' =>
   *    array('defaultValue' => 'ais'
   *          'validator' => new StringValidator()
   *         ),
   *  'Username' =>
   *    array() // no default value => parameter required,
   *            // no validation needed
   * </code>
   *
   * returns array(string=>mixed) validated configuration
   * @throws Exception if there is some error in configuration
   */
  public static function parseAndValidateConfiguration(
      array $description, array $configuration)
  {
    $result = array();
    foreach ($description as $name => $info) {
      // Note: isset() returns false for keys with null value!
      if (array_key_exists($name, $configuration)) {
        $value = $configuration[$name];
        // Validate the value from config file
        if (isset($info['validator'])) {
          try {
            $info['validator']->validate($value);
          }
          catch (Exception $e) {
            throw new Exception('Chyba v konfiguračnej volbe ' . $name .
                                ': ' .$e->getMessage(), null, $e);
          }
        }
        // And set it to config
        $result[$name] = $value;

      } else {
        // If the parameter is optional, we have a default value
        // Note: isset() returns false for keys with null value!
        if (!array_key_exists('defaultValue', $info)) {
          throw new Exception('Konfiguračná voľba ' .
                               $name . ' je povinná');
        }

        $result[$name] = $info['defaultValue'];
      }
    }

    // now check if there are no more items in configurations
    foreach ($configuration as $name => $value) {
      if (!array_key_exists($name, $description)) {
        throw new Exception("Konfiguračná voľba '$name' neexistuje!");
      }
    }
    return $result;
  }

}
