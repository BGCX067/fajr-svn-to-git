<?php
/**
 * Tento súbor obsahuje objekt reprezentujúci kontext aplikácie
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\Request;
use fajr\Response;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use sfStorage;

/**
 * Class representing fajr application context
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Context
{
  /** var Request */
  private $request;

  /** var Response */
  private $response;

  /** var sfStorage*/
  private $session;

  /**
   * Get a request associated with this context
   *
   * @returns Request
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
   * @returns Response
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

  /**
   * Get a session storage for this context
   * @returns sfStorage session storage
   */
  public function getSessionStorage()
  {
    return $this->session;
  }

  /**
   * Set a session storage.
   * @param sfStorage $session
   */
  public function setSessionStorage(sfStorage $session)
  {
    $this->session = $session;
  }


}
