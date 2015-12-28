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
use fajr\libfajr\base\NullTrace;
use fajr\libfajr\base\IllegalStateException;
use fajr\libfajr\login\AIS2LoginException;
use AIS2Utils;
use fajr\libfajr\base\DisableEvilCallsObject;
/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
abstract class AIS2AbstractScreen extends DisableEvilCallsObject implements DialogParent
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

  public function openIfNotAlready(Trace $trace) {
    if ($this->inUse) return;
    $this->executor->requestOpen($trace, $this->data);
    $this->inUse = true;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe,
   */
  public function closeIfNeeded() {
    if (!$this->inUse) return;
    assert($this->openedDialog == null);
    $this->executor->requestClose($this->trace->addChild("Screen close"));
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


  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data) {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog != null) {
      throw new IllegalStateException('V AIS2 screene "'.$this->formName.
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    return $this->executor->spawnDialogExecutor($data);
  }

  public function closeDialog($dialogUid) {
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }
}
?>
