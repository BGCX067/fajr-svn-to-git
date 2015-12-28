<?php

namespace fajr;
use fajr\Request;
use sfStorage;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\login\LoginFactory;

class LoginManager
{
  private $request;
  private $session;

  public function __construct(sfStorage $session, Request $request)
  {
    $this->session = $session;
    $this->request = $request;
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
    if ($login === null) return false;
    if (!$login->logout($connection)) {
      return false;
    }

    $this->session->remove('login/login.class');
    $this->session->remove('server');
    $this->session->regenerate(true);
    // TODO(ppershing): refactor redirects into one place
    if ($server->getLoginType() == 'cosignproxy') {
        // location header set in CosignProxyLogin
        // but we can't exit there because
        // the session wouldn't get dropped
        exit;
    } else {
      FajrUtils::redirect(array(), 'index.php');
      exit;
    }
    assert(false);
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

    FajrUtils::redirect();
    assert(false);
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
      default:
        // TODO(ppershing): throw ConfigError
        assert(false);
    }
  }
}
