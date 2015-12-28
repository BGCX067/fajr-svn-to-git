<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\window\VSES017_administracia_studia as VSES017;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\window\RequestBuilderImpl;
use fajr\libfajr\window\ScreenRequestExecutorImpl;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

class VSES017_factory
{
  private $connection;

  public function __construct(AIS2ServerConnection $serverConnection)
  {
    $this->connection = $serverConnection;
  }

  public function newAdministraciaStudiaScreen(Trace $trace)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\AdministraciaStudiaScreenImpl($trace, $executor, $parser);
  }

  public function newTerminyHodnoteniaScreen(Trace $trace, $idZapisnyList, $idStudium)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\TerminyHodnoteniaScreenImpl(
        $trace, $executor, $parser, $idZapisnyList, $idStudium);
  }

  public function newHodnoteniaPriemeryScreen(Trace $trace, $idZapisnyList)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\HodnoteniaPriemeryScreenImpl(
        $trace, $executor, $parser, $idZapisnyList);
  }
}
