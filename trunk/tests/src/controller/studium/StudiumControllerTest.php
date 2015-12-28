<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for StudiumController class
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use PHPUnit_Framework_TestCase;

use fajr\Request;
use fajr\Response;
use fajr\Context;
use fajr\MockInvocationParameters;
use fajr\libfajr\storage\TemporarilyModifiableStorage;
use fajr\libfajr\storage\MemoryStorage;
use fajr\libfajr\storage\FileStorage;
use fajr\libfajr\pub\regression\fake_data\FakeData;
use fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FakeFactoryImpl;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\window\AIS2ApplicationEnum;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class StudiumControllerTest extends PHPUnit_Framework_TestCase
{
  private $request;
  private $response;
  private $context;
  private $storage;

  private $controller;

  public function setUp()
  {
    $this->response = new Response();
    $this->requestParams = new MockInvocationParameters();
    $time = mktime(0, 0, 0, 1, 8, 2010);
    $this->request = new Request($this->requestParams, $time);
    $this->context = new Context();
    $this->context->setRequest($this->request);
    $this->context->setResponse($this->response);
    $temporary_storage = new MemoryStorage();
    $permanent_storage = new FileStorage(
        array('root_path' => FakeData::getDirectory()));
    $this->storage = new TemporarilyModifiableStorage(
        array('permanent_storage' => $permanent_storage,
              'temporary_storage' => $temporary_storage));
    $this->context->setSessionStorage($this->storage);
    $factory = new VSES017_FakeFactoryImpl($this->storage);

    $this->controller = new StudiumController($factory, $time);

    $this->storage->write('ais/aisApps', array(AIS2ApplicationEnum::ADMINISTRACIA_STUDIA));
  }

  public function testHodnotenia()
  {
    $this->controller->invokeAction(new NullTrace(), 'Hodnotenia', $this->context);
    $data = $this->response->getData();
    $this->assertEquals(10, count($data['hodnotenia']));
    $this->assertEquals(2, count($data['priemery']));
  }
}
