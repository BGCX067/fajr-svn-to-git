<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje triedu reprezentujúcu cosignovú service cookie
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\login;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\base\Preconditions;

/**
 * Trieda reprezentujúcu cosign service cookie
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
final class CosignServiceCookie
{
  const VALUE_PATTERN = '#^[A-Za-z0-9+.@-]+$#';
  const NAME_PATTERN = '#^cosign-[a-zA-Z0-9._-]+$#';
  const DOMAIN_PATTERN = '#^[a-zA-Z0-9._-]+$#';

  /** @var string Cookie value*/
  private $value = null;

  /** @var string Cookie name */
  private $name = null;

  /** @var string Cookie domain*/
  private $domain = null;

  /**
   * Construct a CosignServiceCookie if all arguments are valid
   *
   * @param string $name   Cookie name
   * @param string $value  Cookie value
   * @param string $domain Cookie domain
   */
  public function __construct($name, $value, $domain)
  {
    Preconditions::checkMatchesPattern(self::NAME_PATTERN, $name,
        '$name did not match allowed pattern.');
    Preconditions::checkMatchesPattern(self::VALUE_PATTERN, $value,
        '$value did not match allowed pattern.');
    Preconditions::checkMatchesPattern(self::DOMAIN_PATTERN, $domain,
        '$domain did not match allowed pattern.');
    $this->name = $name;
    $this->value = $value;
    $this->domain = $domain;
  }

  /**
   * Return cookie value
   *
   * @returns string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Return a service cookie name
   *
   * @returns string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Return a cookie domain
   *
   * @returns string
   */
  public function getDomain()
  {
    return $this->domain;
  }

  /**
   * Remove a timestamp from cosign cookie value, if present.
   *
   * Basically this function strips everything after first slash.
   *
   * This function does not validate its input in any way.
   *
   * @param string $value
   * @returns string cookie value without timestamp
   */
  public static function removeTimestamp($value)
  {
    Preconditions::checkIsString($value, '$value should be string.');
    $parts = explode('/', $value, 2);
    return $parts[0];
  }

  /**
   * Replace spaces with plus signs
   *
   * @param string $value
   * @returns string cookie value with spaces replaces with plus signs
   */
  public static function replaceSpaces($value)
  {
    Preconditions::checkIsString($value, '$value should be string.');
    return strtr($value, ' ', '+');
  }

  /**
   * Fix cookie value received via cookie or user input
   * so it can be used in cosign
   *
   * @param string $value
   * @returns string fixed value
   */
  public static function fixCookieValue($value)
  {
    Preconditions::checkIsString($value, '$value should be string.');
    $value = self::removeTimestamp($value);
    $value = self::replaceSpaces($value);
    return $value;
  }

  /**
   * Return a cosign service cookie corresponding to this service
   *
   * @returns CosignServiceCookie service cookie for this service
   */
  public static function getMyCookie()
  {
    if (empty($_SERVER['COSIGN_SERVICE'])) {
      throw new LoginException('Nazov tejto cosign sluzby nie je pritomny v ' .
                               'prostredi. Prosim skontrolujte nastavenie ' .
                               'cosignu.');
    }

    $service = $_SERVER['COSIGN_SERVICE'];

    $cookieName = strtr($service, '.', '_');

    if (empty($_COOKIE[$cookieName])) {
      throw new LoginException('Service cookie pre tuto sluzbu nie je '.
                               'pritomny v prostredi.');
    }

    $value = CosignServiceCookie::fixCookieValue($_COOKIE[$cookieName]);

    $domain = $_SERVER['SERVER_NAME'];

    $cookie = new CosignServiceCookie($service, $value, $domain);

    return $cookie;
  }

}
