<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for AIS2LoginImpl class
 *
 * @package    Fajr
 * @subpackage Tests
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\login;
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\exceptions\AIS2LoginException;
use fajr\libfajr\login\CosignLogin;
use fajr\libfajr\pub\connection\HttpCoonection;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class AIS2LoginImplTest extends PHPUnit_Framework_TestCase
{
  private $responseLoggedIn;
  private $responseNotLogged;
  private $responseLogout;

  private $serverConection;
  private $connection;

  public function setUp()
  {
    $this->responseLoggedIn = file_get_contents(__DIR__.'/testdata/aisLoggedIn.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/aisNotLogged.dat');
    $this->responseLogout = file_get_contents(__DIR__.'/testdata/aisLogout.dat');

    $this->connection = $this->getMock('\fajr\libfajr\pub\connection\HttpConnection');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
        new AIS2ServerUrlMap("ais2.test"), null);
  }

  public function testIsLoggedAlreadyLogged()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseLoggedIn));
    $login = new AIS2LoginImpl();
    $this->assertTrue($login->isLoggedIn($this->serverConnection));
  }

  public function testIsLoggedNotLogged()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $login = new AIS2LoginImpl();
    $this->assertFalse($login->isLoggedIn($this->serverConnection));
  }

  public function testIsLoggedFailure()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue("problem"));
    $login = new AIS2LoginImpl();
    $this->setExpectedException('\Exception');
    $login->isLoggedIn($this->serverConnection);
  }

  public function testLoginOk()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseLoggedIn));
    $login = new AIS2LoginImpl();
    $login->login($this->serverConnection);
  }

  public function testLoginFailure()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $login = new AIS2LoginImpl();
    $this->setExpectedException('\Exception');
    $login->login($this->serverConnection);
  }

}

?>
