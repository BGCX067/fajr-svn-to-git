<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\login;
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\login\CosignAbstractLogin;
use fajr\libfajr\pub\connection\HttpConnection;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignAbstractLoginTest extends PHPUnit_Framework_TestCase
{
  public function testLogout()
  {
    $this->markTestIncomplete();
  }
}

?>
