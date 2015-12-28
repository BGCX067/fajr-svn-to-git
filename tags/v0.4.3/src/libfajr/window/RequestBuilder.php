<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;

interface RequestBuilder
{
  /**
   * Vygeneruje dáta na POST request
   *
   * @param string  $dlgName názov aktuálneho dialógu
   * @param array() $options
   * @returns array() POST data array
   */
  public function buildRequestData($dlgName, array $options);

  /**
   * Vygeneruje url na ktorú treba robiť request
   *
   * @param string $appId id AIS aplikácie
   * @returns string url
   */
  public function getRequestUrl($appId, $formName = null);

  /**
   * Vygeneruje url na ktorú treba robiť request pri inicializovaní
   * novej AIS aplikácie
   *
   * @param string $appClassName
   * @param string $kodAplikacie
   * @returns string url
   */
  public function getAppInitializationUrl(ScreenData $data);

  /**
   * S každým requestom je treba posielať nový serial.
   *
   * @returns int aktuálny serial pre AIS.
   */
  public function newSerial();
}
