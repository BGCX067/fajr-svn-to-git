<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\fake;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\IllegalStateException;
use fajr\libfajr\login\AIS2LoginException;
use AIS2Utils;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\window\DialogParent;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\base\Preconditions;
/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
abstract class FakeAbstractScreen extends DisableEvilCallsObject
    implements FakeDialogParent, LazyDialog
{
  protected $inUse = false;

  protected $openedDialog = null;
  protected $trace = null;
  protected $executor = null;

  /**
   * Konštruktor.
   *
   */
  public function __construct(Trace $trace, FakeRequestExecutor $executor)
  {
    $this->executor = $executor;
    $this->trace = $trace;
  }

  public function openIfNotAlready(Trace $trace)
  {
    if ($this->inUse) {
      return;
    }
    $trace->tlog('opening screen ' . get_class($this));
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
    assert($this->openedDialog === null);
    $trace->tlog('closing screen '.get_class($this));
    $this->inUse = false;
  }

  /**
   * Deštruktor.
   * Zatvorí danú "aplikáciu" v AISe,
   * aby sa nevyčerpal limit otvorených aplikácii na session.
   */
  public function  __destruct()
  {
    $this->closeIfNeeded($this->trace);
  }


  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, array $data)
  {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog != null) {
      throw new IllegalStateException('Vo fake screene "'.get_class($this).
          '" už existuje otvorený dialóg. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    return $this->executor->spawnChild($data);
  }

  public function closeDialog($dialogUid)
  {
    Preconditions::checkContainsInteger($dialogUid);
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }
}
?>
