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
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\HtmlHeader;
use fajr\htmlgen\Table;
use fajr\HtmlTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\injection\Injector;
use fajr\libfajr\AIS2Session;
use fajr\libfajr\base\SystemTimer;
use fajr\libfajr\connection;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\login\AIS2Login;
use fajr\libfajr\pub\login\LoginFactoryImpl;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017; // *
use fajr\presentation\HodnoteniaCallback;
use fajr\presentation\MojeTerminyHodnoteniaCallback;
use fajr\presentation\ZapisanePredmetyCallback;
use fajr\presentation\ZoznamTerminovCallback;
use fajr\TabManager;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * This is "main()" of the fajr. It instantiates all neccessary
 * objects, query ais and renders results.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class Fajr {

  const LOGGED_IN = 0;
  const LOGGED_OUT = 1;

  /**
   * @var Injector $injector dependency injector.
   */
  private $injector;

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
   * WARNING: Must be called before provideConnection().
   */
  private function regenerateSessionOnLogin()
  {
    $login = Input::get('login');
    $krbpwd = Input::get('krbpwd');
    $cosignCookie = Input::get('cosignCookie');

    // FIXME this should be refactored
    if (($login !== null && $krbpwd !== null) || ($cosignCookie !== null)) {
      // we are going to log in, so we get a clean session
      // this needs to be done before a connection
      // is created, because we pass cookie file name
      // that contains session_id into AIS2CurlConnection
      // If we regenerated the session id afterwards,
      // we could not find the cookie file after a redirect
      FajrUtils::dropSession();
    }
  }

  /**
   * Provides login object created from POST-data.
   *
   * @returns AIS2Login
   */
  private function provideLogin()
  {
    // TODO(ppershing): use injector here
    $factory = new LoginFactoryImpl();

    if (FajrConfig::get('Login.Type') == 'cosign') {
      if (Input::get('loginType') == 'cosign') {
        return $factory->newLoginUsingCosignProxy(
            FajrConfig::get('Login.Cosign.ProxyDB'),
            FajrConfig::get('Login.Cosign.CookieName'));
      }
      return null;
    }

    $login = Input::get('login'); Input::set('login', null);
    $krbpwd = Input::get('krbpwd'); Input::set('krbpwd', null);
    $cosignCookie = Input::get('cosignCookie'); Input::set('cosignCookie', null);

    //TODO(ppershing): create hidden field "loginType" in the form
    if ($login !== null && $krbpwd !== null) {
      return $factory->newLoginUsingCosign($login, $krbpwd);
    } else if ($cosignCookie !== null) {
      $cosignCookie = CosignServiceCookie::fixCookieValue($cosignCookie);
      // TODO(anty): change to use correct domain and cookie name
      return $factory->newLoginUsingCookie(new CosignServiceCookie('cosign-filter-ais2.uniba.sk', $cosignCookie, 'ais2.uniba.sk'));
    } else {
      return null;
    }
  }

  // TODO(ppershing): We need to do something about these connections.
  // Currently, this is really ugly solution and should be refactored.
  private $rawStatsConnection;
  private $statsConnection;

  private function provideConnection()
  {
    $connection = new connection\CurlConnection(FajrUtils::getCookieFile());

    $this->rawStatsConnection = new connection\StatsConnection($connection, new SystemTimer());

    $connection = new connection\GzipDecompressingConnection($this->rawStatsConnection, FajrConfig::getDirectory('Path.Temporary'));
    $connection = new connection\AIS2ErrorCheckingConnection($connection);

    $this->statsConnection = new connection\StatsConnection($connection, new SystemTimer());
    return $this->statsConnection;
  }

  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    $this->injector->getInstance('SessionInitializer.class')->startSession();

    $timer = new SystemTimer();

    // TODO(ppershing): use injector here!
    $trace = new NullTrace();

    if (FajrConfig::get('Debug.Trace') === true) {
      $trace = new HtmlTrace($timer, "--Trace--");
    }

    try {
      Input::prepare();

      $this->regenerateSessionOnLogin();
      $connection = $this->provideConnection();
      $this->runLogic($trace, $connection);
    } catch (LoginException $e) {
      if ($connection) {
        FajrUtils::logout($connection);
      }
      DisplayManager::addException($e);
    } catch (Exception $e) {
      DisplayManager::addException($e);
    }

    DisplayManager::setBase(hescape(FajrUtils::basePath()));

    $trace->tlog("everything done, generating html");

    if (FajrConfig::get('Debug.Trace')===true) {
      $traceHtml = $trace->getHtml();
      DisplayManager::addContent('<div class="span-24">' . $traceHtml . 
          '<div> Trace size:' .
          sprintf("%.2f", strlen($traceHtml) / 1024.0 / 1024.0) .
          ' MB</div></div>');
    }
    echo DisplayManager::display();
  }

  public function runLogic(Trace $trace, HttpConnection $connection)
  {
      $serverConnection = new AIS2ServerConnection($connection,
          new AIS2ServerUrlMap(FajrConfig::get('AIS2.ServerName')));
      $timer = new SystemTimer();

      if (Input::get('logout') !== null) {
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
      
      $loggedIn = FajrUtils::isLoggedIn($serverConnection);

      $cosignLogin = $this->provideLogin();
      if (!$loggedIn && $cosignLogin != null) {
          FajrUtils::login($trace->addChild("logging in"), $cosignLogin, $serverConnection);
          $loggedIn = true;
      }

      if (_FAJR_REQUIRE_LOGIN == 'LOGGED_IN' && !$loggedIn) {
        throw new Exception("Interna chyba: uzivatel by mal byt prihlaseny, ale nie je.");
      }

      if (_FAJR_REQUIRE_LOGIN == 'LOGGED_OUT' && $loggedIn) {
        throw new Exception("Interna chyba: uzivatel by nemal byt prihlaseny, ale je.");
      }

      if ($loggedIn) {
        DisplayManager::addContent(
        '<div class=\'logout\'><a class="button negative" href="'.FajrUtils::linkUrl(array('logout'=>true)).'">
        <img src="images/door_in.png" alt=""/>Odhlásiť</a></div>'
        );
        $screenFactory = new VSES017\VSES017_factory($serverConnection);
        $adminStudia = $screenFactory->newAdministraciaStudiaScreen($trace);
        
        if (Input::get('studium') === null) Input::set('studium',0);
        
        $zoznamStudii = $adminStudia->getZoznamStudii($trace->addChild("Get Zoznam Studii:"));
        $zoznamStudiiTable = new Table(TableDefinitions::zoznamStudii(), 'studium',
          array('tab' => Input::get('tab')));
        $zoznamStudiiTable->addRows($zoznamStudii->getData());
        $zoznamStudiiTable->setOption('selected_key', Input::get('studium'));
        $zoznamStudiiTable->setOption('collapsed', true);

        $zoznamStudiiCollapsible = new Collapsible(new HtmlHeader('Zoznam štúdií'), $zoznamStudiiTable, true);

        DisplayManager::addContent($zoznamStudiiCollapsible->getHtml());    
        
        $zapisneListy = $adminStudia->getZapisneListy($trace->addChild('getZapisneListy'), Input::get('studium'));
        
        $zapisneListyTable = new
          Table(TableDefinitions::zoznamZapisnychListov(),
            'list', array('studium' => Input::get('studium'),
              'tab'=>Input::get('tab')));
        
        if (Input::get('list') === null) {
          $tmp = $zapisneListy->getData();
          $lastList = end($tmp);
          Input::set('list', $lastList['index']);
        }
        
        $zapisneListyTable->addRows($zapisneListy->getData());
        $zapisneListyTable->setOption('selected_key', Input::get('list'));
        $zapisneListyTable->setOption('collapsed', true);

        $zapisneListyCollapsible = new Collapsible(new HtmlHeader('Zoznam zápisných listov'), $zapisneListyTable, true);

        DisplayManager::addContent($zapisneListyCollapsible->getHtml());
        
        
        $terminyHodnotenia = $screenFactory->newTerminyHodnoteniaScreen(
              $trace,
              $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, Input::get('list')),
              $adminStudia->getStudiumIdFromZapisnyListIndex($trace, Input::get('list')));
        
        if (Input::get('tab') === null) Input::set('tab', 'TerminyHodnotenia');
        $tabs = new TabManager('tab', array('studium'=>Input::get('studium'),
              'list'=>Input::get('list')));
        // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
        // objekty, ktore v konstruktore robia requesty
        $hodnoteniaScreen = $screenFactory->newHodnoteniaPriemeryScreen(
              $trace,
              $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, Input::get('list')));
        $tabs->addTab('TerminyHodnotenia', 'Moje skúšky',
              new MojeTerminyHodnoteniaCallback($trace, $terminyHodnotenia, $hodnoteniaScreen));
        $tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky',
              new ZoznamTerminovCallback($trace, $terminyHodnotenia, $hodnoteniaScreen));
        $tabs->addTab('ZapisnyList', 'Zápisný list',
              new ZapisanePredmetyCallback($trace, $terminyHodnotenia));
        $tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
            new HodnoteniaCallback($trace, $hodnoteniaScreen));

        $tabs->setActive(Input::get('tab'));
        DisplayManager::addContent($tabs->getHtml());
        ;
        $version = '<div>Fajr verzia '.hescape(Version::getVersionString()).'</div>';
        DisplayManager::addContent($version);
        $statistics = "<div> Fajr made ".$this->statsConnection->getTotalCount().
                " requests and downloaded ".$this->rawStatsConnection->getTotalSize().
                " bytes (".$this->statsConnection->getTotalSize().
                " bytes uncompressed) of data from AIS2 in ".
                sprintf("%.3f", $this->statsConnection->getTotalTime()).
                " seconds. It took ".sprintf("%.3f", $timer->getElapsedTime()).
                " seconds to generate this page.</div>";
        DisplayManager::addContent($statistics);
      }
      else
      {
        if (FajrConfig::get('Login.Type') == 'password') {
          DisplayManager::addContent('loginBox', true);
        }
        else if (FajrConfig::get('Login.Type') == 'cosign') {
          DisplayManager::addContent('cosignLoginBox', true);
        }
        else {
          throw new Exception('Nespravna hodnota konfiguracnej volby Login.Type');
        }
        DisplayManager::addContent('warnings', true);
        DisplayManager::addContent('terms', true);
        DisplayManager::addContent('credits', true);
        $version = "<div class='version prepend-1 span-21 last increase-line-height'>\n<strong>Verzia fajru:</strong> \n";
        $version .= hescape(Version::getVersionString());
        $version .= '</div>';
        DisplayManager::addContent($version);
        DisplayManager::addContent(Version::getChangelog(), false);
      }
  }
}
