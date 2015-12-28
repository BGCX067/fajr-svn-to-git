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
use fajr\libfajr\window\DialogData;
use fajr\libfajr\pub\base\Trace;

interface DialogParent
{
  /**
   * @returns DialogRequestExecutor
   */
  public function openDialogAndGetExecutor(Trace $trace, $uid, DialogData $data);
  public function closeDialog($uid);
}
