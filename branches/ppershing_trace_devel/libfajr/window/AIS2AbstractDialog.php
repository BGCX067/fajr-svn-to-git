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
namespace fajr\libfajr\window;

use fajr\libfajr\base\Trace;
use fajr\libfajr\window\DialogData;
use fajr\libfajr\window\DialogParent;
use fajr\libfajr\base\DisableEvilCallsObject;
/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
class AIS2AbstractDialog extends DisableEvilCallsObject implements DialogParent
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

  const DIALOG_NAME_PATTERN = '@dm\(\)\.openDialog\("(?P<dialogName>[^"]+)",@';

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(Trace $trace, DialogParent $parent, DialogData $data)
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
    if ($this->inUse) return;
    $this->executor = $this->parent->openDialogAndGetExecutor($trace, $this->uid, $this->data);
    $this->formName = $this->executor->requestOpen($trace);
    $this->inUse = true;
    $this->terminated = false;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function closeIfNeeded(Trace $trace)
  {
    if (!$this->inUse) return;
    if (!$this->terminated) {
      $this->executor->requestClose($trace);
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

  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data) {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog !== null) {
      throw new IllegalStateException('V AIS2 screene "'.$this->formName.
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    $executor = $this->executor->spawnChild($data, $this->formName);
    return $executor;
  }

  public function closeDialog($dialogUid) {
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }

}
?>
