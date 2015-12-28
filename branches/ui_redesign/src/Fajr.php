<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * The main logic of fajr application.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
namespace fajr;
use Exception;
use fajr\ArrayTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\injection\Injector;
use fajr\libfajr\AIS2Session;
use fajr\libfajr\base\SystemTimer;
use fajr\libfajr\connection;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;
use fajr\Request;
use fajr\Response;
use fajr\Context;
use fajr\Statistics;
use fajr\Version;

/**
 * This is "main()" of the fajr. It instantiates all neccessary
 * objects, query ais and renders results.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class Fajr {

  /**
   * @var Injector $injector dependency injector.
   */
  private $injector;

  /**
   * @var Context $context application context
   */
  private $context;

  /**
   * @var Statistics $statistics
   */
  private $statistics;

  /**
   * Constructor.
   *
   * @param Injector $injector dependency injector.
   */
  public function __construct(Injector $injector)
  {
    $this->injector = $injector;
  }

  /**
   * @returns true iff the user initiated a login
   */
  private function shouldLogin()
  {
    return $this->context->getRequest()->hasParameter('loginType');
  }

  /**
   * WARNING: Must be called before provideConnection().
   */
  private function regenerateSessionOnLogin()
  {
    if (!$this->shouldLogin()) return;
   
    // we are going to log in, so we get a clean session
    // this needs to be done before a connection
    // is created, because we pass cookie file name
    // that contains session_id into AIS2CurlConnection
    // If we regenerated the session id afterwards,
    // we could not find the cookie file after a redirect
    FajrUtils::dropSession();
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
  private function provideLogin()
  {
    $factory = $this->injector->getInstance('LoginFactory.class');

    $request = $this->context->getRequest();

    $loginType = $request->getParameter("loginType");
    $login = $request->getParameter('login');
    $krbpwd = $request->getParameter('krbpwd');
    $cosignCookie = $request->getParameter('cosignCookie');

    // we don't need this info in the global scope anymore
    $request->clearParameter('login');
    $request->clearParameter('krbpwd');
    $request->clearParameter('cosignCookie');

    if (empty($loginType)) return null;

    if ($loginType == 'cosign') {
      if (FajrConfig::get('Login.Type') == 'cosign') {
        return $factory->newLoginUsingCosignProxy(
            FajrConfig::get('Login.Cosign.ProxyDB'),
            FajrConfig::get('Login.Cosign.CookieName'));
      }
      return null;
    }
    else if ($loginType == 'password') {
      if ($login == null || $krbpwd == null) {
        // TODO(anty): maybe throw an exception? (and display login form...)
        return null;
      }

      return $factory->newLoginUsingCosign($login, $krbpwd);
    }
    else if ($loginType == 'cookie') {
      if ($cosignCookie == null) {
        // TODO(anty): maybe throw an exception? (and display login form...)
        return null;
      }
      $cosignCookie = CosignServiceCookie::fixCookieValue($cosignCookie);
      return $factory->newLoginUsingCookie(
          new CosignServiceCookie(FajrConfig::get('Login.Cosign.CookieName'),
                                  $cosignCookie,
                                  FajrConfig::get('AIS2.ServerName')));
    }

    return null;
  }

  private function provideConnection()
  {
    $curlOptions = $this->injector->getParameter('CurlConnection.options');
    $connection = new connection\CurlConnection($curlOptions, FajrUtils::getCookieFile());

    $connection = $this->statistics->hookRawConnection($connection);

    $connection = new connection\GzipDecompressingConnection($connection, FajrConfig::getDirectory('Path.Temporary'));
    $connection = new connection\AIS2ErrorCheckingConnection($connection);

    return $this->statistics->hookFinalConnection($connection);
  }

  /**
   * Set an exception to be displayed in DisplayManager
   * @param Exception $ex
   */
  private function setException(Exception $ex) {
    $response = $this->context->getResponse();
    $response->set('exception', $ex);
    $response->set('showStackTrace',
                   FajrConfig::get('Debug.Exception.ShowStacktrace'));
    $response->setTemplate('exception');
  }

  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    $this->injector->getInstance('SessionInitializer.class')->startSession();

    $trace = $this->injector->getInstance('Trace.class');
    $this->statistics = $this->injector->getInstance('Statistics.class');
    $this->displayManager = $this->injector->getInstance('DisplayManager.class');
    $this->context = $this->injector->getInstance('Context.class');

    try {
      Input::prepare();

      $this->regenerateSessionOnLogin();
      $connection = $this->provideConnection();
      $this->runLogic($trace, $connection);
    } catch (LoginException $e) {
      if ($connection) {
        FajrUtils::logout($connection);
      }

      $this->setException($e);
    } catch (Exception $e) {
      $this->setException($e);      
    }

    $trace->tlog("everything done, generating html");

    if (FajrConfig::get('Debug.Trace')===true) {
      $this->context->getResponse()->set('trace', $trace);
    }

    $this->context->getResponse()->set('base', FajrUtils::basePath());
    $this->context->getResponse()->set('language', 'sk');
    try {
      echo $this->displayManager->display($this->context->getResponse());
    }
    catch (Exception $e) {
      throw new Exception('Chyba pri renderovaní template: '.$e->getMessage(),
                          null, $e);
    }
  }

  public function runLogic(Trace $trace, HttpConnection $connection)
  {
    $response = $this->context->getResponse();
    $response->set('version', new Version());
    $response->set('banner_debug', FajrConfig::get('Debug.Banner'));
    // TODO(anty): toto by chcelo nastavovat nejako lepsie
    $response->set('banner_beta',
        FajrConfig::get('AIS2.ServerName') == 'ais2-beta.uniba.sk');
    $response->set('google_analytics',
                   FajrConfig::get('GoogleAnalytics.Account'));
    $response->set('serverName', FajrConfig::get('AIS2.ServerName'));
    $response->set('cosignCookieName', FajrConfig::get('Login.Cosign.CookieName'));
    $response->set('instanceName', FajrConfig::get('AIS2.InstanceName'));

    $serverConnection = new AIS2ServerConnection($connection,
        new AIS2ServerUrlMap(FajrConfig::get('AIS2.ServerName')));

    $this->context->setAisConnection($serverConnection);

    $action = $this->context->getRequest()->getParameter('action',
                                           'studium.MojeTerminyHodnotenia');

    if ($action == 'logout') {
      FajrUtils::logout($serverConnection);
      // TODO(anty): fix this in a better way
      if (FajrConfig::get('Login.Type') == 'cosign') {
        // location header set in CosignProxyLogin
        // but we can't exit there because
        // the session wouldn't get dropped
        exit;
      }
      FajrUtils::redirect(array(), 'index.php');
    }
    // TODO(anty): refactor this
    else if ($action == 'termsOfUse') {
      $response->setTemplate('termsOfUse');
      return;
    }

    $loggedIn = FajrUtils::isLoggedIn($serverConnection);

    $cosignLogin = $this->provideLogin();
    if (!$loggedIn && $cosignLogin != null) {
        FajrUtils::login($trace->addChild("logging in"), $cosignLogin,
                         $serverConnection);
        $loggedIn = true;
    }

    if ($loggedIn) {
      $controller = $this->injector->getInstance('Controller.class');

      $response->set("action", $action);
      $controller->invokeAction($trace, $action, $this->context);
      $response->set('statistics', $this->statistics);
    }
    else
    {
      if (FajrConfig::get('Login.Type') == 'password') {
        $response->setTemplate('welcome');
      }
      else {
        $response->setTemplate('welcomeCosign');
      }
    }

    
  }
}
