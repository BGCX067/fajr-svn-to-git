<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\IllegalStateException;
use fajr\libfajr\login\AIS2LoginException;
use AIS2Utils;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\window\LazyDialog;

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Martin Králik <majak47@gmail.com>
 */
abstract class AIS2AbstractScreen extends DisableEvilCallsObject
    implements DialogParent, LazyDialog
{
  protected $inUse = false;

  protected $openedDialog = null;
  protected $trace = null;
  protected $data = null;
  protected $executor = null;

  /**
   * Konštruktor.
   *
   */
  public function __construct(Trace $trace, ScreenRequestExecutor $executor, ScreenData $data)
  {
    $this->executor = $executor;
    $this->trace = $trace;
    $this->data = $data;
  }

  public function openIfNotAlready(Trace $trace)
  {
    if ($this->inUse) {
      return;
    }
    $this->executor->requestOpen($trace, $this->data);
    $this->inUse = true;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe,
   */
  public function closeIfNeeded(Trace $trace)
  {
    if (!$this->inUse) {
      return;
    }
    assert($this->openedDialog == null);
    $this->executor->requestClose($trace);
    $this->inUse = false;
  }

  /**
   * Deštruktor.
   * Zatvorí danú "aplikáciu" v AISe,
   * aby sa nevyčerpal limit otvorených aplikácii na session.
   */
  public function  __destruct()
  {
    $this->closeIfNeeded($this->trace->addChild("Screen close"));
  }


  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data)
  {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog != null) {
      throw new IllegalStateException('V AIS2 screene "'.$this->data->appClassName.
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    return $this->executor->spawnDialogExecutor($data);
  }

  public function closeDialog($dialogUid)
  {
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }
}
?>
