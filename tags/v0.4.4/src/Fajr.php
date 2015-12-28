<?php
/**
 * The main logic of fajr application.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;

use Exception;
use fajr\ArrayTrace;
use fajr\Context;
use fajr\controller\DispatchController;
use fajr\exceptions\SecurityException;
use fajr\exceptions\ValidationException;
use fajr\injection\Injector;
use fajr\libfajr\AIS2Session;
use fajr\libfajr\base\SystemTimer;
use fajr\libfajr\connection;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\window\AIS2MainScreenImpl;
use fajr\modules\ControllerInjectorModule;
use fajr\Request;
use fajr\Response;
use fajr\Statistics;
use fajr\util\FajrUtils;
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
    $session = $this->context->getSessionStorage();

    $serverList = FajrConfig::get('AIS2.ServerList');
    $serverName = FajrConfig::get('AIS2.DefaultServer');

    if (($server = $session->read('server')) !== null) {
      if ($session->read('login/login.class') === null) {
        throw new Exception('Fajr is in invalid state. Delete cookies and try again.');
      }
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
   * Set an exception to be displayed.
   * @param Exception $ex
   */
  private function setException(Exception $ex) {
    if ($this->context == null) {
      // May happen if exception occured before or in context
      // instantiation. We don't know how to handle the
      // exception in this case, so just pass it to the
      // outer exception handler
      throw $ex;
    }
    $response = $this->context->getResponse();

    // Note: We can't store function agruments from
    // stacktrace for template rendering, because
    // it can hold cyclic dependency to Context
    // and thus makes order of destruction unpredictable.
    $info = FajrUtils::extractExceptionInfo($ex);
    $response->set('exception', $info);

    $response->set('showStackTrace',
                   FajrConfig::get('Debug.Exception.ShowStacktrace'));
    
    $response->setTemplate('exception');
  }

  /**
   * Save information about security violation for analysis.
   * @param SecurityException
   * @returns void
   */
  private function logSecurityException(Exception $e) {
    
  }


  public function render(Response $response)
  {
    try {
      $displayManager = $this->injector->getInstance('DisplayManager.class');
      echo $displayManager->display($response);
    } catch (Exception $e) {
      throw new Exception('Chyba pri renderovaní template '.
          $response->getTemplate().':' .$e->getMessage(),
                          null, $e);
    }
  }


  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    try {
      $trace = $this->injector->getInstance('Trace.class');
      $this->statistics = $this->injector->getInstance('Statistics.class');
      $this->context = $this->injector->getInstance('Context.class');

      $session = $this->context->getSessionStorage();
      $response = $this->context->getResponse();
      $loginManager = new LoginManager($session, $this->context->getRequest());

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
      $this->logSecurityException($e);
      if (!FajrConfig::get('Debug.Exception.ShowStacktrace')) {
        die("Internal error");
      } else {
        die($e);
      }
    } catch (Exception $e) {
      $this->setException($e);
      // Note: We MUST unset this exception, because it's
      // stacktrace holds cyclic references to context
      // and therefore the order of destruction of all objects 
      // is really random.
      unset($e);
    }

    if ($trace !== null) {
      $trace->tlog("everything done, rendering template");

      if ($trace instanceof \fajr\ArrayTrace) {
        $response->set('trace', $trace);
      } else {
        $response->set('trace', null);
      }
    }

    $this->render($this->context->getResponse());
  }

  private function setResponseFields(Response $response)
  {
    $response = $this->context->getResponse();
    $response->set('version', new Version());
    $response->set('banner_debug', FajrConfig::get('Debug.Banner'));
    $response->set('google_analytics',
                   FajrConfig::get('GoogleAnalytics.Account'));
    $response->set('base', FajrUtils::basePath());
    $response->set('language', 'sk');

    $response->set('availableServers', array());
    $response->set('currentServer', array('isBeta'=>false, 'instanceName'=>'Chyba'));

    $server = $this->getServer();
    $serverList = FajrConfig::get('AIS2.ServerList');
    $response->set('availableServers', $serverList);
    $response->set('currentServer', $server);

    $response->set('aisVersion', null);
    $response->set('aisVersionIncompatible', false);
  }

  public function runLogic(Trace $trace, HttpConnection $connection)
  {
    $session = $this->context->getSessionStorage();
    $loginManager = new LoginManager($session, $this->context->getRequest());
    $server = $this->getServer();
    $serverConnection = new AIS2ServerConnection($connection,
        new AIS2ServerUrlMap($server->getServerName()));
      
    $action = $this->context->getRequest()->getParameter('action',
                                           'studium.MojeTerminyHodnotenia');
    $response = $this->context->getResponse();

    if ($action == 'logout') {
      $loginManager->logout($serverConnection);
      FajrUtils::redirect(array(), 'index.php');
      exit();
    } else if ($action == 'termsOfUse') {
      // TODO(anty): refactor this
      $response->setTemplate('termsOfUse');
      return;
    }

    if ($loginManager->shouldLogin()) {
      $factory = $this->injector->getInstance('LoginFactory.class');
      $loginManager->login($trace->addChild("Logging in..."),
          $server, $factory, $serverConnection);
      $loggedIn = false; // login makes redirect on success
    } else {
      $loggedIn = $loginManager->isLoggedIn($serverConnection);
    }

    if ($loggedIn) {
      $controllerInjector = new Injector(array(new
            ControllerInjectorModule($serverConnection, $server, $session)));
      $mainScreen = $controllerInjector->getInstance('AIS2MainScreen.class');

      if (($aisVersion = $session->read('ais/aisVersion')) == null) {
        $aisVersion = $mainScreen->getAisVersion($trace->addChild('Get AIS version'));
        $session->write('ais/aisVersion', $aisVersion);
      }
      if (($aisApps = $session->read('ais/aisApps')) == null) {
        $aisApps = $mainScreen->getAllAvailableApplications($trace->addChild('Get all applications'));
        $session->write('ais/aisApps', $aisApps);
      }
      if (($userName = $session->read('ais/aisUserName')) == null) {
        $userName = $mainScreen->getFullUserName($trace->addChild('Get user name'));
        $session->write('ais/aisUserName', $userName);
      }
      $response->set('aisVersion', $aisVersion);
      $response->set('aisVersionIncompatible', 
        !($aisVersion >= regression\VersionRange::getMinVersion() &&
          $aisVersion <= regression\VersionRange::getMaxVersion()));
      $response->set('aisUserName', $userName);

      $controller = new DispatchController($controllerInjector,
          $this->injector->getParameter('controller.dispatchMap'));

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
        case 'nologin':
          $response->setTemplate('welcomeDemo');
          break;
        default:
          throw new Exception("Invalid type of login");
      }
    }
  }
}
