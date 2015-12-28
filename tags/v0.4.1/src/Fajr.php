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
use sfSessionStorage;

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

  private function provideConnection()
  {
    $curlOptions = $this->injector->getParameter('CurlConnection.options');
    $connection = new connection\CurlConnection($curlOptions, FajrUtils::getCookieFile());

    $connection = $this->statistics->hookRawConnection($connection);

    $connection = new connection\GzipDecompressingConnection($connection, FajrConfig::getDirectory('Path.Temporary'));
    $connection = new connection\AIS2ErrorCheckingConnection($connection);

    return $this->statistics->hookFinalConnection($connection);
  }

  public function getServer()
  {
    $request = $this->context->getRequest();
    $session = $this->injector->getInstance("Session.Storage.class");

    $serverList = FajrConfig::get('AIS2.ServerList');
    $serverName = FajrConfig::get('AIS2.DefaultServer');

    if (($server = $session->read('server')) !== null) {
      return $server;
    }

    if ($request->getParameter("serverName")) {
      $serverName = $request->getParameter("serverName");
      if (!isset($serverList[$serverName])) {
        throw new SecurityException("Invalid serverName!");
      }
    }
    
    assert(isset($serverList[$serverName]));
    return $serverList[$serverName];
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
   * Save information about security violation for analysis.
   * @param SecurityException
   * @returns void
   */
  private function logSecurityException(SecurityException $e) {
    
  }

  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    $trace = $this->injector->getInstance('Trace.class');
    $this->statistics = $this->injector->getInstance('Statistics.class');
    $this->displayManager = $this->injector->getInstance('DisplayManager.class');
    $this->context = $this->injector->getInstance('Context.class');

    $session = $this->injector->getInstance('Session.Storage.class');
    $loginManager = new LoginManager($session, $this->context->getRequest());
    $response = $this->context->getResponse();

    try {
      Input::prepare();

      // we are going to log in, so we get a clean session
      // this needs to be done before a connection
      // is created, because we pass cookie file name
      // that contains session_id into AIS2CurlConnection
      if ($loginManager->shouldLogin()) {
        $session->regenerate(true);
      }


      $connection = $this->provideConnection();
      $this->setResponseFields($response);
      $this->runLogic($trace, $connection);
    } catch (LoginException $e) {
      if ($connection) {
        FajrUtils::logout($connection);
      }

      $this->setException($e);
    } catch (SecurityException $e) {
      die($e);
      $this->logSecurityException($e);
      $response->setTemplate("securityViolation");
    } catch (Exception $e) {
      die($e);
      $this->setException($e);
    }

    $trace->tlog("everything done, rendering template");

    if (FajrConfig::get('Debug.Trace') === true) {
      $response->set('trace', $trace);
    } else {
      $response->set('trace', null);
    }

    try {
      echo $this->displayManager->display($this->context->getResponse());
    } catch (Exception $e) {
      throw new Exception('Chyba pri renderovaní template '.
          $this->context->getResponse()->getTemplate().':' .$e->getMessage(),
                          null, $e);
    }
  }

  private function setResponseFields(Response $response)
  {
    $response = $this->context->getResponse();
    $response->set('version', new Version());
    $response->set('banner_debug', FajrConfig::get('Debug.Banner'));
    $response->set('google_analytics',
                   FajrConfig::get('GoogleAnalytics.Account'));
    $response->set('instanceName', FajrConfig::get('AIS2.InstanceName'));
    $response->set('base', FajrUtils::basePath());
    $response->set('language', 'sk');

    $server = $this->getServer();
    $serverList = FajrConfig::get('AIS2.ServerList');
    $response->set('availableServers', $serverList);
    $response->set('currentServer', $server);
//    $response->set('serverName', $server->getServerName());
//    $response->set('banner_beta', $server->isBeta());
//    $response->set('cosignCookieName', $server->getCosignCookieName());
  }

  public function runLogic(Trace $trace, HttpConnection $connection)
  {
    $session = $this->injector->getInstance('Session.Storage.class');
    $loginManager = new LoginManager($session, $this->context->getRequest());
    $server = $this->getServer();
    $serverConnection = new AIS2ServerConnection($connection,
        new AIS2ServerUrlMap($server->getServerName()));
      
    $this->context->setAisConnection($serverConnection);

    $action = $this->context->getRequest()->getParameter('action',
                                           'studium.MojeTerminyHodnotenia');
    $response = $this->context->getResponse();

    if ($action == 'logout') {
      $loginManager->logout($serverConnection);
      // unless there is an error, logout redirects and ends script execution
    } else if ($action == 'termsOfUse') {
      // TODO(anty): refactor this
      $response->setTemplate('termsOfUse');
      return;
    }

    if ($loginManager->shouldLogin()) {
      $factory = $this->injector->getInstance('LoginFactory.class');
      $session->write('server', $server);
      $loginManager->login($trace->addChild("Logging in..."),
          $server, $factory, $serverConnection);
      $loggedIn = false; // login makes redirect on success
      $session->remove('server');
    } else {
      $loggedIn = $loginManager->isLoggedIn($serverConnection);
    }

    if ($loggedIn) {
      $controller = $this->injector->getInstance('Controller.class');

      $response->set("action", $action);
      $controller->invokeAction($trace, $action, $this->context);
      $response->set('statistics', $this->statistics);
    }
    else
    {
      $server = $this->getServer();
      switch ($server->getLoginType()) {
        case 'password':
          $response->setTemplate('welcome');
          break;
        case 'cosign':
          $response->setTemplate('welcomeCosign');
          break;
        case 'cosignproxy':
          $response->setTemplate('welcomeCosignProxy');
          break;
        default:
          throw new Exception("Invalid type of login");
      }
    }
  }
}
