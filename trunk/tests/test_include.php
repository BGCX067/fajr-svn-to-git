<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
require_once '../third_party/symfony_di/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

require_once '../third_party/symfony_storage/sfStorageAutoloader.php';
sfStorageAutoloader::register();

require_once '../third_party/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

require_once '../src/libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(__DIR__.'/../src', true);
ini_set('error_reporting', E_ALL | E_STRICT);


?>
