<?php
/**
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\config\ServerConfig;
use fajr\exceptions\SecurityException;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\login\LoginFactory;
use fajr\Request;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\libfajr\login\CosignProxyLogin;

class LoginManager
{
  private $request;
  private $session;
  private $response;

  public function __construct(sfStorage $session, Request $request, Response $response)
  {
    $this->session = $session;
    $this->request = $request;
    $this->response = $response;
  }

  /**
   * @returns true iff the user initiated a login
   */
  public function shouldLogin()
  {
    // TODO(ppershing): refactor templates
    // to use action=login and special controller
    return $this->request->hasParameter('loginType');
  }

  public function isLoggedIn(AIS2ServerConnection $connection)
  {
    $login = $this->session->read('login/login.class');
    if ($login === null) return false;

    return $login->isLoggedIn($connection) ||
           $login->ais2Relogin($connection);
  }

  /**
   * Odhlási z Cosignu a zmaže lokálne cookies.
   */
  public function logout(AIS2ServerConnection $connection)
  {
    $login = $this->session->read('login/login.class');
    $server = $this->session->read('server');

    // It is better to remove all session information also
    // in case when logout fails. Otherwise it may be not
    // possible for user to logout from fajr and this
    // is greater security risk than leaving active cookies
    // on server side.
    $this->session->remove('login/login.class');
    $this->session->remove('server');
    // wipe out all other session data
    // Note, calling $session->regenerate() preserve data
    // so we force destroy in old way
    session_destroy();


    if ($login === null) return false;
    if (!$login->logout($connection)) {
      return false;
    }

    if ($server->getLoginType() == 'cosignproxy') {
      // Redirect na hlavnu odhlasovaciu stranku univerzity
      $this->response->redirect(CosignProxyLogin::COSIGN_LOGOUT);
      $this->response->clearCookie($_SERVER[ 'COSIGN_SERVICE' ], '/', '');
    } else {
      $this->response->redirect(array(), 'index.php');
    }
  }

  public function login(Trace $trace, ServerConfig $serverConfig, LoginFactory $factory, AIS2ServerConnection $connection)
  {
    $login = $this->provideLogin($serverConfig, $factory, $this->request);
    if ($login === null) return false;
    $trace->tlog("logging in");
    if (!$login->login($connection)) {
      return false;
    }
    $trace->tlog("logged in correctly.");
    $this->session->write('login/login.class', $login);
    $this->session->write('server', $serverConfig);

    $this->response->redirect();
  }

  private function assertSecurity($condition, $message)
  {
    if ($condition !== true) {
      throw new SecurityException($message);
    }
  }

  /**
   * Provides login object created from POST-data
   * or null if login info is not (fully) present in the request.
   *
   * This function should be called only once (it will
   * return null on subsequent calls).
   *
   * @returns Login login instance recognized
   */
  private function provideLogin(ServerConfig $serverConfig, LoginFactory $factory, Request $request)
  {
    $loginType = $request->getParameter("loginType");
    $login = $request->getParameter('login');
    $password = $request->getParameter('password');
    $cosignCookie = $request->getParameter('cosignCookie');

    // we don't need this info in the global scope anymore
    $request->clearParameter('login');
    $request->clearParameter('password');
    $request->clearParameter('cosignCookie');

    if (empty($loginType)) return null;

    switch ($serverConfig->getLoginType()) {
      case 'password':
        $this->assertSecurity($loginType === 'password',
                              "Wrong login type $loginType");
        $this->assertSecurity($login !== null, 'Login field missing');
        $this->assertSecurity($password !== null, 'Password field missing');

        if ($login === '' || $password === '') {
          return null;
        }
        return $factory->newLoginUsingPassword($login, $password);
        break;
      case 'cosign':
        if ($loginType === 'cosigncookie') {
          if ($cosignCookie === '') {
            return null;
          }
          $cosignCookie = CosignServiceCookie::fixCookieValue($cosignCookie);
          return $factory->newLoginUsingCosignCookie(
              new CosignServiceCookie($serverConfig->getCosignCookieName(),
                $cosignCookie, $serverConfig->getServerName()));
        } else if ($loginType == 'cosignpassword') {
          if ($login === null || $password === null) {
            return null;
          }
          return $factory->newLoginUsingCosignPassword($login, $password);
        } else {
          $this->assertSecurity(false, "Wrong loginType $loginType");
        }
        break;
      case 'cosignproxy':
        $this->assertSecurity($loginType === 'cosignproxy',
                              "Wrong loginType $loginType");
        return $factory->newLoginUsingCosignProxy(
            $serverConfig->getCosignProxyDB(),
            $serverConfig->getCosignCookieName());
      case 'nologin':
        $this->assertSecurity($loginType === 'nologin',
                              "Wrong loginType $loginType");
        return $factory->newNoLogin();
      default:
        // TODO(ppershing): throw ConfigError
        assert(false);
    }
  }
}
