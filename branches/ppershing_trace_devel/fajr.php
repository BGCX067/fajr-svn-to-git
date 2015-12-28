<?php
/* {{{
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

use fajr\HtmlTrace;
use fajr\libfajr\base\NullTrace;
use fajr\libfajr\base\SystemTimer;
use fajr\libfajr\connection;
use fajr\libfajr\login\CosignLogin;
use fajr\libfajr\login\AIS2CookieLogin;

use fajr\libfajr\window\VSES017_administracia_studia as VSES017; // *

if (!defined('_FAJR')) {
  die('<html><head>'.
      '<title>Varovanie</title>'.
      '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
      '</head><body>'.
      '<h1>Varovanie</h1>'.
      '<p>Máte zle nastavený server, tento súbor by nemal byť '.
      'priamo prístupný. Prosím nastavte server tak, aby sa dalo '.
      'dostať len k podadresáru <code>web</code> a použite '.
      '<code>index.php</code> v ňom</p>'.
      '</body></html>');
}

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Bratislava');
mb_internal_encoding("UTF-8");

// Pretoze v session ukladam objekty libfajru, treba nacitat definicie
// tried skor, ako sa nacitava session
require_once 'libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(dirname(__FILE__), true);
require_once 'libfajr/Assert.php';

if (!FajrConfig::isConfigured()) {
  DisplayManager::addContent('notConfigured', true);
  echo DisplayManager::display();
  session_write_close();
  die();
}

class Fajr {
  private function startSession()
  {
    $sessionLifeTime = 36000;
    session_cache_expire($sessionLifeTime/60);
    session_set_cookie_params($sessionLifeTime, '/', '.' . $_SERVER['HTTP_HOST']);
    // cache expire, u servera
    ini_set("session.gc_maxlifetime", $sessionLifeTime);
    ini_set("session.cookie_lifetime", $sessionLifeTime);
    // custom cache expire je mozny iba pre custom session adresar
    session_save_path(FajrConfig::getDirectory('Path.Temporary.Sessions'));
    session_start();
  }

  public function run()
  {
    $timer = new SystemTimer();
    $connection = null;
    $statsConnection = null;
    $rawStatsConnection = null;
    $trace = new NullTrace();

    if (FajrConfig::get('Debug.Trace') === true) {
      $trace = new HtmlTrace($timer, "--Trace--");
    }

    try
    {
      Input::prepare();

      $login = Input::get('login'); Input::set('login', null);
      $krbpwd = Input::get('krbpwd'); Input::set('krbpwd', null);
      $cosignCookie = Input::get('cosignCookie'); Input::set('cosignCookie', null);

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

      $connection = new connection\CurlConnection(FajrUtils::getCookieFile());

      $rawStatsConnection = new connection\StatsConnection($connection, new SystemTimer());
      $connection = $rawStatsConnection;

      $connection = new connection\GzipDecompressingConnection($connection, FajrConfig::getDirectory('Path.Temporary'));
      $connection = new connection\AIS2ErrorCheckingConnection($connection);

      $statsConnection = new connection\StatsConnection($connection, new SystemTimer());
      $connection = $statsConnection;
      $simpleConnection = new connection\HttpToSimpleConnectionAdapter($connection);

      AIS2Utils::connection($simpleConnection); // toto tu je docasne

      if (Input::get('logout') !== null) {
        FajrUtils::logout($connection);
        FajrUtils::redirect();
      }

      if ($login !== null && $krbpwd !== null) {
        $loggedIn = FajrUtils::login(new CosignLogin($login, $krbpwd), $connection);
        $login = null;
        $krbpwd = null;
      } else if ($cosignCookie !== null) {
        $loggedIn = FajrUtils::login(new AIS2CookieLogin($cosignCookie), $connection);
        $cosignCookie = null;
      } else {
        $loggedIn = FajrUtils::isLoggedIn();
      }

      if ($loggedIn) {
        DisplayManager::addContent(
        '<div class=\'logout\'><a class="button negative" href="'.FajrUtils::linkUrl(array('logout'=>true)).'">
        <img src="images/door_in.png" alt=""/>Odhlásiť</a></div>'
        );
        $adminStudia = new VSES017\AdministraciaStudiaScreen($trace, $simpleConnection);
        
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
        
        
        $terminyHodnotenia = new
          VSES017\TerminyHodnoteniaScreen(
              $trace,
              $simpleConnection,
              $adminStudia->getIdZapisnyList($trace, Input::get('list')),
              $adminStudia->getIdStudium($trace, Input::get('list')));
        
        if (Input::get('tab') === null) Input::set('tab', 'TerminyHodnotenia');
        $tabs = new TabManager('tab', array('studium'=>Input::get('studium'),
              'list'=>Input::get('list')));
        // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
        // objekty, ktore v konstruktore robia requesty
        $hodnoteniaScreen = new VSES017\HodnoteniaPriemeryScreen(
              $trace, $simpleConnection,
              $adminStudia->getIdZapisnyList($trace,
                Input::get('list')));
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
        $statistics = "<div> Fajr made ".$statsConnection->getTotalCount().
                " requests and downloaded ".$rawStatsConnection->getTotalSize().
                " bytes (".$statsConnection->getTotalSize().
                " bytes uncompressed) of data from AIS2 in ".
                sprintf("%.3f", $statsConnection->getTotalTime()).
                " seconds. It took ".sprintf("%.3f", $timer->getElapsedTime()).
                " seconds to generate this page.</div>";
        DisplayManager::addContent($statistics);
      }
      else
      {
        DisplayManager::addContent('loginBox', true); 
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
    catch (AIS2LoginException $e) {
      if ($connection) FajrUtils::logout($connection);
      DisplayManager::addException($e);
    }
    catch (Exception $e)
    {
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

  public function main()
  {
    $this->startSession();
    $this->run();
  }
}

$fajr = new Fajr();
$fajr->main();
session_write_close();
