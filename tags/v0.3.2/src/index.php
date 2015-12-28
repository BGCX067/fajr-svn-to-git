<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Bootstraps the whole application.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\injection\Injector;
use fajr\injection\Module;
use fajr\modules\CurlConnectionOptionsModule;
use fajr\modules\SessionInitializerModule;
use fajr\modules\TraceModule;
use Loader;
use sfServiceContainerAutoloader;

/**
 * Wrong www root detection.
 */
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

// TODO(ppershing): create helper objects and configuration modules for these constants
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Bratislava');
mb_internal_encoding("UTF-8");

// register Symfony autoloader first, because ours will eat the loading instead.
require_once '../third_party/symfony_di/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();
// register our autoloader
require_once 'libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(dirname(__FILE__), true);
// TODO(ppershing): move this to libfajr/Loader.php as that is the right place for it
require_once 'libfajr/Assert.php';

// is there configuration.php file present?
if (!FajrConfig::isConfigured()) {
  DisplayManager::addContent('notConfigured', true);
  echo DisplayManager::display();
  session_write_close();
  die();
}

// bootstrapping whole application
$modules = array(
    new CurlConnectionOptionsModule(),
    new SessionInitializerModule(),
    new TraceModule());
$injector = new Injector($modules);
$fajr = new Fajr($injector);
$fajr->run();
session_write_close();
