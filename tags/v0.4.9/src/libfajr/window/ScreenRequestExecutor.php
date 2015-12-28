<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;
use fajr\libfajr\pub\base\Trace;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\SimpleConnection;

interface ScreenRequestExecutor
{
  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function requestOpen(Trace $trace, ScreenData $data);

  public function requestContent(Trace $trace);

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function requestClose(Trace $trace);

  public function getRequestUrl();

  public function doRequest(Trace $trace, $options);

  public function spawnDialogExecutor(DialogData $data);
}
?>
