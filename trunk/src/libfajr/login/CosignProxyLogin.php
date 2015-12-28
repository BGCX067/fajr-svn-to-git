<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * 
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\login;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\data_manipulation\CosignProxyFileParser;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\exceptions\NotImplementedException;
use fajr\libfajr\pub\exceptions\LoginException;

/**
 * Trieda reprezentujúca prihlasovanie pomocou cosign proxy
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class CosignProxyLogin extends CosignAbstractLogin
{
  /** @var string $proxyDir path to directory containing cosign proxy files */
  private $proxyDir;

  /** @var string $proxyCookieName name of proxy cookie to retrieve */
  private $proxyCookieName;

  /**
   * @param string $proxyDir path to directory containing cosign proxy files
   */
  public function  __construct($proxyDir, $proxyCookieName)
  {
    Preconditions::checkIsString($proxyDir, '$proxyDir should be string.');
    Preconditions::checkIsString($proxyCookieName,
        '$proxyCookieName should be string');
    $this->proxyDir = $proxyDir;
    $this->proxyCookieName = $proxyCookieName;
  }

  public function login(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    if (empty($_SERVER['REMOTE_USER'])) {
      throw new LoginException('Nie je nastaveny cosign username');
    }

    $myCookie = CosignServiceCookie::getMyCookie();

    $filename = $this->proxyDir . '/' . $myCookie->getName() . '=' .
                $myCookie->getValue();

    $parser = new CosignProxyFileParser();
    $cookies = $parser->parseFile(new NullTrace(), $filename);

    if (empty($cookies[$this->proxyCookieName])) {
      throw new LoginException('Neviem najst relevantny proxy cookie');
    }

    $cookie = $cookies[$this->proxyCookieName];
    $connection->addCookie($cookie->getName(), $cookie->getValue(),
                  0, '/', $cookie->getDomain());
    
    return true;
  }

  public function isLoggedIn(AIS2ServerConnection $unused)
  {
    return !empty($_SERVER['REMOTE_USER']);
  }

  public function logout(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $connection->clearCookies();
    
    return true;
  }

}
