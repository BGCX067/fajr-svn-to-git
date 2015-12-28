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

use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

class RequestBuilderImpl implements RequestBuilder
{
  private $serial = 0;
  private $server;

  public function __construct(AIS2ServerUrlMap $server)
  {
    $this->server = $server;
  }

  /**
   * Vygeneruje nové sériové číslo používané v XML protokole na komunikáciu s AISom.
   *
   * @returns int Nové seriové číslo v poradí.
   */
  public function newSerial()
  {
    return $this->serial++;
  }

  /**
   * Vytvorí url XML interfacu pre komunikáciu s "aplikáciou" tejto obrazovky.
   *
   * @param string $appId AIS2 id aplikácie.
   * @returns string Url.
   */
  public function getRequestUrl($appId, $formName = null)
  {
    $url = $this->server->getWebUiServletUrl();
    // TODO(ppershing): use url builder here!
    $url .= '?appId=' . $appId;
    if ($formName !== null) {
      $url .= '&form='.$formName;
    }
    return $url;
  }


  public function getAppInitializationUrl(ScreenData $data)
  {
    $url = $this->server->getWebUiServletUrl();
    // TODO(ppershing): use url builder here!
    $url .= '?appClassName=' . $data->appClassName;
    if ($data->additionalParams !== null) {
      foreach ($data->additionalParams as $key => $value) {
        $url .= '&' . $key . '=' . $value;
      }
    }
    return $url;
  }

  /**
   * Experimentalna funkcia snažiaca sa zovšeobecniť dodatočné requesty jednotlivých AIS aplikácií.
   * Je veľmi pravdepodobné, že sa bude meniť.
   *
   * @param string  $dlgName názov aktuálneho dialógu
   * @param array() $options špeciálne nastavenia, viď kód.
   * @returns array() POST dáta.
   */
  public function buildRequestData($dlgName, array $options)
  {
    $events = true;
    $eventClass = 'avc.ui.event.AVCActionEvent';
    $app = true;
    $command = null;
    $compName = null;
    $appProperties = array();
    $embObj = null;
    $appProperties = array();
    $objProperties = array();
    $changedProperties = null;
    extract($options, EXTR_IF_EXISTS);

    if (!isset($appProperties['activeDlgName'])) {
      $appProperties['activeDlgName'] = $dlgName;
    }

    $xml_spec = '<request><serial>'.$this->newSerial().'</serial>';
    if ($events === true) {
      $xml_spec .= '<events><ev>';
      if ($dlgName !== null) {
        $xml_spec .= '<dlgName>'.$dlgName.'</dlgName>';
      }
      if ($compName !== null) {
        $xml_spec .= '<compName>'.$compName.'</compName>';
      }
      $xml_spec .= '<event class=\''.$eventClass.'\'>';
      if ($command !== null) {
        $xml_spec .= '<command>'.$command.'</command>';
      }
      $xml_spec .= '</event></ev></events>';
    }
    $xml_spec .= '<changedProps>';
    if ($app === true) {
      $xml_spec .= '<changedProperties><objName>app</objName><propertyValues>';
      foreach ($appProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues></changedProperties>';
    }

    if ($dlgName !== false) {
      $xml_spec .= '<changedProperties><objName>'.$dlgName.'</objName><propertyValues>';
      foreach ($objProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues><embObjChProps>';

      if ($embObj !== null) {
        $xml_spec .= '<changedProperties><objName>' . $embObj['objName'] .
            '</objName><propertyValues><nameValue><name>dataView</name>' .
            '<isXml>true</isXml><value><![CDATA[<root><selection>';
        if (isset($embObj['dataView']) && is_array($embObj['dataView'])) {
          foreach ($embObj['dataView'] as $name => $value) {
            $xml_spec .= '<'.$name.'>'.$value.'</'.$name.'>';
          }
        }
        $xml_spec .= '</selection>';
        if (isset($embObj['visibleBuffers'])) {
          $xml_spec .= '<visibleBuffers>' . $embObj['visibleBuffers'] . '</visibleBuffers>';
        }
        if (isset($embObj['loadedBuffers'])) {
          $xml_spec .= '<loadedBuffers>'.$embObj['loadedBuffers'].'</loadedBuffers>';
        }
        $xml_spec .= '
                </root>
              ]]></value>
            </nameValue>
            <nameValue>
              <name>editMode</name>
              <isXml>false</isXml>
              <value>false</value>
            </nameValue>
          </propertyValues>
          <embObjChProps isNull=\'true\'/>
        </changedProperties>';
      }
      $xml_spec .= '</embObjChProps></changedProperties>';
    }
    
    if (is_array($changedProperties)) {
      $xml_spec .= '<changedProperties><propertyValues>';
      foreach ($changedProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues></changedProperties>';
    }
    
    $xml_spec .= '</changedProps></request>';
    
    return array('xml_spec' => $xml_spec);
  }
}
