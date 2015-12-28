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
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\window\LazyDialog;

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FakeAbstractDialog extends DisableEvilCallsObject
    implements FakeDialogParent, LazyDialog
{
  protected $parent = null;
  protected $terminated = false;
  protected $formName = null;
  protected $inUse = false;
  protected $executor;
  protected $openedDialog = null;

  private $trace = null;
  private $uid;
  protected $data;

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers  Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(Trace $trace, FakeDialogParent $parent, array $data)
  {
    $this->trace = $trace;
    $this->parent = $parent;
    $this->data = $data;
    $this->uid = random();
  }

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function openIfNotAlready(Trace $trace)
  {
    if ($this->inUse) {
      return;
    }
    $this->executor = $this->parent->openDialogAndGetExecutor($trace, $this->uid, $this->data);
    $trace->tlog('opening dialog ' . get_class($this));
    $this->inUse = true;
    $this->terminated = false;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function closeIfNeeded(Trace $trace)
  {
    if (!$this->inUse) {
      return;
    }
    if (!$this->terminated) {
      $trace->tlog('closing dialog '.get_class($this));
    }
    $this->inUse = false;
    $this->parent->closeDialog($this->uid);
  }

  /**
   * Deštruktor.
   * Zatvorí danú "aplikáciu" v AISe,
   * aby sa nevyčerpal limit otvorených aplikácii na session.
   * Toto správenie nebolo pozorované pri dialógoch, ale pre istotu to tu je.
   */
  public function  __destruct()
  {
    $this->closeIfNeeded($this->trace);
  }

  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, array $data)
  {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog !== null) {
      throw new IllegalStateException('V AIS2 fake screene "' . get_class($this) .
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    $executor = $this->executor->spawnChild($data);
    return $executor;
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
