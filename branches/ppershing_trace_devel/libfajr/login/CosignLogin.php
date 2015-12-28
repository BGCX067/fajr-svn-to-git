<?php
/* {{{
Copyright (c) 2010 Martin Sucha
Copyright (c) 2010 Martin Králik

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
namespace fajr\libfajr\login;

use fajr\libfajr\connection\HttpConnection;
use fajr\libfajr\base\NullTrace;
use \Exception;
/**
 * Trieda reprezentujúca prihlasovanie pomocou cosign
 *
 * @author majak, ms
 */
class CosignLogin extends AIS2AbstractLogin {

  const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';

  const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';
  const MAIN_PAGE = 'https://ais2.uniba.sk';

  private $username = null;
  private $krbpwd = null;
  
  public function __construct($username, $krbpwd) {
    assert($username != null);
    assert($krbpwd != null);
    $this->username = $username;
    $this->krbpwd = $krbpwd;
  }

  const COSIGN_ERROR_PATTERN1 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>([^<]*)\<\/div\>@';
  const COSIGN_ERROR_PATTERN2 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<b\>([^<]*)\<\/b\>@';
  const COSIGN_ERROR_PATTERN3 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>\<b\>([^<]*)\<\/b\>@';

  public function login(HttpConnection $connection) {
    $login = $this->username;
    $krbpwd = $this->krbpwd;

    // Username a password si nebudeme pamatat dlhsie ako treba
    $this->username = null;
    $this->krbpwd = null;

    $data = $connection->get(new NullTrace(), self::LOGIN);
    if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data)) {
      assert($login !== null && $krbpwd !== null);
      $data = $connection->post(new NullTrace(), self::COSIGN_LOGIN, array('ref' => self::LOGIN,
            'login'=> $login, 'password' => $krbpwd));
      if (!preg_match('@\<base href\="https://ais2\.uniba\.sk/ais/portal/pages/portal_layout\.jsp"\>@', $data)) {
        if (($reason = match($data, self::COSIGN_ERROR_PATTERN1)) ||
            ($reason = match($data, self::COSIGN_ERROR_PATTERN2)) ||
            ($reason = match($data, self::COSIGN_ERROR_PATTERN3))) {
          throw new Exception('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
          
        }
        throw new Exception('Nepodarilo sa prihlásiť, dôvod neznámy.');
      }
      $this->loggedIn = true;
      return true;
    }
    $this->loggedIn = true; // naozaj?
    return true;
  }

  public function logout(HttpConnection $connection) {
    $connection->post(new NullTrace(), self::COSIGN_LOGOUT, array('verify' => 'Odhlásiť', 'url'=> self::MAIN_PAGE));
    return parent::logout($connection);
  }

}
