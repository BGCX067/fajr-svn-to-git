<?php
/**
 * Holds configuration about one AIS server.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\validators\ChoiceValidator;
use fajr\validators\StringValidator;
use fajr\util\ConfigUtils;
/**
 * Contains all configurable options of AIS server in Fajr.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ServerConfig
{
  /** @var array(string=>mixed) */
  private $config;

  /** use libfajr backend */
  const BACKEND_LIBFAJR = 'libfajr';
  /** use fake backend with virtual data */
  const BACKEND_FAKE = 'fake';

  protected static function getParameterDescription()
  {
    $booleanValidator = new ChoiceValidator(array(true, false));
    $stringValidator = new StringValidator();

    return array(
        'Server.Beta' =>
          array('defaultValue' => false,
                'validator' => $booleanValidator),
        'Server.InstanceName' =>
          array('defaultValue' => 'AIS2',
                'validator' => $stringValidator),
        'Server.Name' =>
          array('validator' => $stringValidator),
        'Login.Cosign.CookieName' =>
          array('defaultValue' => null,
                'validator' => $stringValidator),
        'Login.Cosign.ProxyDB' =>
          array('defaultValue' => null,
                'validator' => $stringValidator),
        'Login.Type' =>
          array('validator' => new ChoiceValidator(
                  array('password', 'cosign', 'cosignproxy', 'nologin'))),
        'Backend' =>
          array('defaultValue' => self::BACKEND_LIBFAJR,
                'validator' => new ChoiceValidator(
                  array(self::BACKEND_LIBFAJR, self::BACKEND_FAKE))),
        );
  }

  public function __construct(array $options)
  {
    $this->config = ConfigUtils::parseAndValidateConfiguration(
        $this->getParameterDescription(),
        $options);
  }

  /**
   * Is this server instance running beta version of ais?
   *
   * @returns boolean
   */
  public function isBeta()
  {
    return $this->config['Server.Beta'];
  }

  /**
   * Returns server name (domain).
   *
   * @returns string server name
   */
  public function getServerName()
  {
    return $this->config['Server.Name'];
  }

  /**
   * Returns cosign cookie name needed for login.
   *
   * @returns string cookie name
   */
  public function getCosignCookieName() {
    return $this->config['Login.Cosign.CookieName'];
  }

  /**
   * Returns database directory for cosign proxy
   *
   * @returns string db dir
   */
  public function getCosignProxyDB() {
    return $this->config['Login.Cosign.ProxyDB'];
  }

  /**
   * Return type of login used with this server
   *
   * @returns string
   */
  public function getLoginType()
  {
    return $this->config['Login.Type'];
  }

  /**
   * Return type of ais server, typically AIS2 or AIS2-beta
   *
   * @returns string
   */
  public function getInstanceName()
  {
    return $this->config['Server.InstanceName'];
  }

  /**
   * Returns type of backend used to connect to this server.
   * @see BACKEND_LIBFAJR, BACKEND_FAKE
   *
   * @returns string
   */
  public function getBackendType()
  {
    return $this->config['Backend'];
  }

}
