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

require_once 'PHPUnit/Framework.php';
require_once '../src/libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(__DIR__.'/../src', true);
ini_set('error_reporting', E_ALL | E_STRICT);
?>
