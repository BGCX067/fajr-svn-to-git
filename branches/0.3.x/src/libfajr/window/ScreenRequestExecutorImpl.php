<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;
use fajr\libfajr\pub\base\Trace;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\SimpleConnection;

class ScreenRequestExecutorImpl extends DisableEvilCallsObject
    implements ScreenRequestExecutor
{
  protected $data;
  protected $requestBuilder;
  protected $appId;
  protected $formName;
  protected $connection;

  /**
   * Konštruktor.
   *
   */
  public function __construct(RequestBuilder $requestBuilder, SimpleConnection $connection)
  {
    $this->requestBuilder = $requestBuilder;
    $this->connection = $connection;
  }


  const FORM_NAME_PATTERN = '@dm\(\)\.openMainDialog\("(?P<formName>[^"]*)","(?P<name>[^"]*)","(?P<formId>[^"]*)",[0-9]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*\);@';

  public function parseFormNameFromResponse($response)
  {
    $matches = array();
    if (preg_match(self::FORM_NAME_PATTERN, $response, $matches)) {
      return $matches['formName'];
    } else {
      throw new Exception('Neviem nájsť formName v odpovedi ' .
                          'vo fáze inicializácie triedy '.get_class().'!');
    }
  }

  const APPID_PATTERN = '@\<body onload\=\'window\.setTimeout\("WebUI_init\(\\\"([0-9]+)\\\", \\\"ais\\\", \\\"ais/webui2\\\"\)", 1\)\'@';

  public function parseAppIdFromResponse($response)
  {
    $matches = array();
    if (preg_match(self::APPID_PATTERN, $response, $matches)) {
      return $matches[1];
    } else {
      throw new Exception('Neviem nájsť appId v odpovedi vo fáze inicializácie triedy '.__CLASS__.'!');
    }
  }


  public function spawnChild(DialogData $data, $parentFormName)
  {
    return new AIS2DialogRequestExecutor($this->requestBuilder, $this->connection, $data, $this->parentAppId, $parentFormName);
  }

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function requestOpen(Trace $trace, ScreenData $data)
  {
    $trace->tlog("open screen");

    $url = $this->requestBuilder->getAppInitializationUrl($data);

    $response = $this->connection->request(
        $trace->addChild("get app id"),
        $url);

    $this->appId = $this->parseAppIdFromResponse($response);

    $response = $this->doRequest(
        $trace->addChild("Init command"),
        array('eventClass' => 'avc.ui.event.AVCComponentEvent',
              'command' => 'INIT',
        ));

    $this->formName = $this->parseFormNameFromResponse($response);
  }

  public function requestContent(Trace $trace)
  {
    $response = $this->connection->request($trace,
        $this->getRequestUrl());
    return $response;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function requestClose(Trace $trace)
  {
    $response = $this->doRequest($trace,
        array('eventClass' => 'avc.framework.webui.WebUIKillEvent',
              'command' => 'CLOSE',
        ));
  }

  public function getRequestUrl()
  {
    return $this->requestBuilder->getRequestUrl($this->appId, $this->formName);
  }

  public function doRequest(Trace $trace, $options)
  {
    $data = $this->requestBuilder->buildRequestData($this->formName, $options);
    return $this->connection->request($trace, $this->getRequestUrl(), $data);
  }

  public function spawnDialogExecutor(DialogData $data)
  {
    return new DialogRequestExecutor($this->requestBuilder, $this->connection,
                                     $data, $this->appId, $this->formName);
  }

}
?>
