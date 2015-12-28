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

class DialogRequestExecutor extends DisableEvilCallsObject
{
  protected $data;
  protected $requestBuilder;
  protected $parentFormName;
  protected $parentAppId;
  protected $formName = null;
  protected $connection;

  const DIALOG_NAME_PATTERN = '@dm\(\)\.openDialog\("(?P<dialogName>[^"]+)",@';

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers  Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(
      RequestBuilder $requestBuilder,
      SimpleConnection $connection,
      DialogData $data,
      $parentAppId,
      $parentFormName)
  {
    $this->data = $data;
    $this->requestBuilder = $requestBuilder;
    $this->parentAppId = $parentAppId;
    $this->parentFormName = $parentFormName;
    $this->connection = $connection;
  }

  public function spawnChild(DialogData $data, $parentFormName)
  {
    return new DialogRequestExecutor($this->requestBuilder, $this->connection,
                                     $data, $this->parentAppId, $parentFormName);
  }

  public function parseDialogNameFromResponse($response)
  {
    $matches = array();
    if (preg_match(self::DIALOG_NAME_PATTERN, $response, $matches)) {
      return $matches['dialogName'];
    } else {
      return null;
    }
  }

  public function requestOpen($trace)
  {
    $options = array('dlgName' => $this->parentFormName,
                     'compName' => $this->data->compName,
                    );
    
    if ($this->data->embObjName !== null) {
      $options['embObj'] = array(
        'objName' => $this->data->embObjName,
        'dataView' => array(
          'activeIndex' =>  $this->data->index,
          'selectedIndexes' => $this->data->index,
        ),
      );
    }
    
    $data = $this->requestBuilder->buildRequestData(
        $this->parentFormName, $options);
    $response = $this->connection->request($trace->addChild("dialog opening request"),
        $this->getRequestUrl($this->parentAppId), $data);

    $dialogName = $this->parseDialogNameFromResponse($response);
    if ($dialogName === null) {
      throw new Exception('Nepodarilo sa nájsť názov dialógu pre triedu '.get_class().'.');
    }
    $this->formName = $dialogName;
    return $dialogName;
  }

  public function requestContent(Trace $trace)
  {
    $response = $this->connection->request($trace,
        $this->requestBuilder->getRequestUrl($this->parentAppId, $this->formName));
    return $response;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function requestClose(Trace $trace)
  {
    $response = $this->doRequest($trace,
        array('eventClass' => 'avc.ui.event.AVCComponentEvent',
              'command' => 'CLOSE',
        ));
  }

  public function getRequestUrl()
  {
    return $this->requestBuilder->getRequestUrl($this->parentAppId);
  }

  public function doRequest(Trace $trace, $options)
  {
    $data = $this->requestBuilder->buildRequestData($this->formName, $options);
    return $this->connection->request($trace, $this->getRequestUrl($this->parentAppId), $data);
  }

}
?>
