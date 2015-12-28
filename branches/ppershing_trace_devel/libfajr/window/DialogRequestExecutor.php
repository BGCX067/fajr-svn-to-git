<?php
namespace fajr\libfajr\window;
use fajr\libfajr\base\Trace;
use AIS2Utils;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;

class DialogRequestExecutor extends DisableEvilCallsObject
{
  protected $data;
  protected $requestBuilder;
  protected $parentFormName;
  protected $parentAppId;
  protected $formName = null;

  const DIALOG_NAME_PATTERN = '@dm\(\)\.openDialog\("(?P<dialogName>[^"]+)",@';

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(DialogData $data, RequestBuilder $requestBuilder, $parentAppId,
      $parentFormName)
  {
    $this->data = $data;
    $this->requestBuilder = $requestBuilder;
    $this->parentAppId = $parentAppId;
    $this->parentFormName = $parentFormName;
  }

  public function spawnChild(DialogData $data, $parentFormName)
  {
    return new AIS2DialogRequestExecutor($data, $this->requestBuilder, $this->parentAppId, $parentFormName);
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
    $data = $this->requestBuilder->buildRequestData(
        $this->parentFormName,
        array('dlgName' => $this->parentFormName,
              'compName' => $this->data->compName,
              'embObj' => array(
                'objName' => $this->data->embObjName,
                'dataView' => array(
                  'activeIndex' =>  $this->data->index,
                  'selectedIndexes' => $this->data->index,
                ),
              ),
            ));
    $response = AIS2Utils::request($trace->addChild("dialog opening request"),
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
    $response = AIS2Utils::request($trace,
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
    return AIS2Utils::request($trace, $this->getRequestUrl($this->parentAppId), $data);
  }

}
?>
