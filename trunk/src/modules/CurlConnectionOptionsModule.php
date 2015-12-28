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

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
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
  private $config;

  public function __construct(FajrConfig $config) {
    $this->config = $config;
  }

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
        CURLOPT_USERAGENT => $this->config->get(FajrConfigOptions::USER_AGENT),
        CURLOPT_ENCODING => 'gzip',
        );
    // overridnutie adresara pre certifikaty
    if ($this->config->get(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES)) {
      $options[CURLOPT_CAPATH] = $this->config->getDirectory(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES);
    }
    $container->setParameter('CurlConnection.options', $options);
  }
}
