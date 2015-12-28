<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
abstract class AIS2AbstractScreen
{
  protected $appId = null;
  protected $appClassName = null;
  protected $identifiers = null;

  protected $formName = null;
  protected $data = null;
  protected $inUse = false;
  public function getFormName() {
    return $this->formName;
  }

  public $openedDialog = false;
  protected $requestBuilder = null;

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct($appClassName, $identifiers)
  {
    $this->requestBuilder = new AIS2\RequestBuilderImpl();
    $this->appClassName = $appClassName;
    $this->identifiers = $identifiers;
  }
  public function getXmlInterfaceLocation() {
    return $this->requestBuilder->getRequestUrl($this->getAppId());
  }

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function open() {
    if ($this->inUse) return;
    $this->inUse = true;
    
    $location = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appClassName='.$this->appClassName.$this->identifiers.'&viewer=web&antiCache='.random();
    $response = AIS2Utils::request($location);
    $this->setAppId($response);

    $response = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request><serial>'.$this->getSerial().'</serial><events><ev><event class=\'avc.ui.event.AVCComponentEvent\'><command>INIT</command></event></ev></events></request>'));
    if (preg_match("/Neautorizovaný prístup!/", $response)) {
      // logoutni aby to nemusel robit uzivatel
      throw new AIS2LoginException("AIS hlási neautorizovaný prístup -
        pravdepodobne vypršala platnosť cookie");
    }
    $this->setFormName($response);

    $this->data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&form='.$this->formName.'&antiCache='.random());
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe,
   */
  public function close() {
    if (!$this->inUse) return;
    AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request><serial>'.$this->getSerial().'</serial><events><ev><event class=\'avc.framework.webui.WebUIKillEvent\'/></ev></events></request>'));
    $this->inUse = false;
  }

  /**
   * Deštruktor.
   * Zatvorí danú "aplikáciu" v AISe,
   * aby sa nevyčerpal limit otvorených aplikácii na session.
   */
  public function  __destruct()
  {
    $this->close();
  }

  
  public function getAppId()
  {
    $this->open();
    return $this->appId;
  }
  
  const APPID_PATTERN = '@\<body onload\=\'window\.setTimeout\("WebUI_init\(\\\"([0-9]+)\\\", \\\"ais\\\", \\\"ais/webui2\\\"\)", 1\)\'@';

  public function parseAppIdFromResponse($response) {
    $matches = array();
    if (preg_match(self::APPID_PATTERN, $response, $matches)) {
      return $matches[1];
    } else {
      return null;
    }
  }

  /**
   * Nastaví atribút $appId, ktorý pomocou regulárneho výrazu nájde vo vstupných dátach.
   * @param string $response Odpoveď AISu v HTML formáte z inicializačnej časti komunikácie.
   */
  protected function setAppId($response)
  {
    $appId = $this->parseAppIdFromResponse($response);
    if ($appId !== null) {
      $this->appId = $appId;
    } else {
      throw new Exception('Neviem nájsť appId v odpovedi vo fáze inicializácie triedy '.__CLASS__.'!');
    }
  }

  const FORM_NAME_PATTERN = '@dm\(\)\.openMainDialog\("(?P<formName>[^"]*)","(?P<name>[^"]*)","(?P<formId>[^"]*)",[0-9]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*\);@';

  public function parseFormNameFromResponse($response) {
    $matches = array();
    if (preg_match(self::FORM_NAME_PATTERN, $response, $matches)) {
      return $matches['formName'];
    } else {
      return null;
    }
  }

  /**
   * Nastaví atribút $formName, ktorý pomocou regulárneho výrazu nájde vo vstupných dátach.
   * @param string $response Odpoveď AISu v HTML formáte z inicializačnej časti komunikácie.
   */
  protected function setFormName($response)
  {
    $name = $this->parseFormNameFromResponse($response);
    if ($name !== null) {
      $this->formName = $name;
    } else {
      throw new Exception('Neviem nájsť formName v odpovedi vo fáze inicializácie triedy '.get_class().'!');
    }
  }

  public function getSerial() {
    return $this->requestBuilder->newSerial();
  }

  public function requestData($options, $debug=false) {
    $data = $this->requestBuilder->buildRequestData($this->formName, $options);
    return AIS2Utils::request($this->getXmlInterfaceLocation(), $data);
  }
}
?>
