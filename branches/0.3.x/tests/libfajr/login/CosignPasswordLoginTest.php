<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Validator class
 *
 * @package    Fajr
 * @subpackage Tests
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\login;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\login\CosignPasswordLogin;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignPasswordLoginTest extends PHPUnit_Framework_TestCase
{
  private $responseAlreadyLogged;
  private $responseNotLogged;
  private $responseWrongPassword1;
  private $responseWrongPassword2;
  private $responseWrongPassword3;
  private $responseLoginOk;

  private $serverConection;
  private $connection;

  public function setUp()
  {
    $this->responseAlreadyLogged = file_get_contents(__DIR__.'/testdata/cosignAlreadyLogged.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/cosignNotLogged.dat');
    $this->responseLoginOk = $this->responseAlreadyLogged;
    $this->responseWrongPassword1 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword.dat');
    $this->responseWrongPassword2 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword2.dat');
    $this->responseWrongPassword3 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword3.dat');

    $this->connection = $this->getMock('\fajr\libfajr\pub\connection\HttpConnection');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
        new AIS2ServerUrlMap("ais2.test"));
  }

  public function testLoginOk()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->connection->expects($this->once())
                     ->method('post')
                     ->will($this->returnValue($this->responseLoginOk));
    $login = new CosignPasswordLogin('user', 'passwd');
    $login->login($this->serverConnection);
  }

  public function testLoginWrong1()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->connection->expects($this->once())
                     ->method('post')
                     ->will($this->returnValue($this->responseWrongPassword1));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($this->serverConnection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@Password or Account Name incorrect@", $msg);
    }
  }

  public function testLoginWrong2()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->connection->expects($this->once())
                     ->method('post')
                     ->will($this->returnValue($this->responseWrongPassword2));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($this->serverConnection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@nesprávne meno alebo heslo@", $msg);
    }
  }

  public function testLoginWrong3()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->connection->expects($this->once())
                     ->method('post')
                     ->will($this->returnValue($this->responseWrongPassword3));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($this->serverConnection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@Chyba - zadané nesprávne meno alebo heslo@", $msg);
    }
  }

  public function testLoggedIn()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseAlreadyLogged));
    $login = new CosignPasswordLogin('user', 'passwd');
    $this->assertTrue($login->isLoggedIn($this->serverConnection));
  }

  public function testNotLogged()
  {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $login = new CosignPasswordLogin('user', 'passwd');
    $this->assertFalse($login->isLoggedIn($this->serverConnection));
  }
}

?>
