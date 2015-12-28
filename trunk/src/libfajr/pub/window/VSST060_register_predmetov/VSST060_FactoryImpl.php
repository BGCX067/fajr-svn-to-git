<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\window\VSST060_register_predmetov;

use fajr\libfajr\window\VSST060_register_predmetov as VSST060;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutorImpl;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

class VSST060_FactoryImpl implements VSST060_Factory
{
  private $connection;

  public function __construct(AIS2ServerConnection $serverConnection)
  {
    $this->connection = $serverConnection;
  }

  public function newRegisterPredmetovScreen(Trace $trace)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSST060\RegisterPredmetovScreenImpl($trace, $executor, $parser);
  }
}
