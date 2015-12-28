<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt reprezentujúci kontext aplikácie
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\Request;
use fajr\Response;
use fajr\libfajr\pub\connection\AIS2ServerConnection;

/**
 * Class representing fajr application context
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Context
{

  /** var AIS2ServerConnection */
  private $aisConnection;

  /** var Request */
  private $request;

  /** var Response */
  private $response;

  /**
   * Return a ServerConnection
   *
   * @return ServerConnection connection to AIS server
   */
  public function getAisConnection()
  {
    return $this->aisConnection;
  }

  /**
   * Set a ServerConnection
   *
   * @param ServerConnection $aisConnection
   */
  public function setAisConnection(AIS2ServerConnection $aisConnection)
  {
    $this->aisConnection = $aisConnection;
  }

  /**
   * Get a request associated with this context
   *
   * @return Request
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Set a Request for this context
   *
   * @param Request $request 
   */
  public function setRequest($request)
  {
    $this->request = $request;
  }

  /**
   * Get a Response for this context
   * @return Response
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Set a Response for this context
   * @param Response $response 
   */
  public function setResponse($response)
  {
    $this->response = $response;
  }


}