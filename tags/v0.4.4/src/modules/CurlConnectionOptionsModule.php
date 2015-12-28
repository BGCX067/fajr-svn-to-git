<?php
/**
 * Injector module for CurlConnection.options
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\FajrConfig;
use fajr\injection\Module;
use sfServiceContainerBuilder;

/**
 * Injector module for CurlConnection options parameter.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class CurlConnectionOptionsModule implements Module
{
  /**
   * Configure CurlConnection.options
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $options = array(
        CURLOPT_FORBID_REUSE => false, // Keepalive konekcie
        CURLOPT_FOLLOWLOCATION => true, // Redirecty pri prihlasovani/odhlasovani
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => true,
        CURLOPT_USERAGENT => FajrConfig::get('Connection.UserAgent'),
        );
    // overridnutie adresara pre certifikaty
    if (FajrConfig::get('SSL.CertificatesDir')) {
      $options[CURLOPT_CAPATH] = FajrConfig::get('SSL.CertificatesDir');
    }
    $container->setParameter('CurlConnection.options', $options);
  }
}
