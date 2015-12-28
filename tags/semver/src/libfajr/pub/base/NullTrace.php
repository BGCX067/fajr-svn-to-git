<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Dummy Trace object doing nothing
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\base;
use fajr\libfajr\pub\base\Trace;

/**
 * Represents tracer object which does nothing. It shoud be convenient to
 * pass around as default value of trace parameter.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class NullTrace implements Trace
{
  public function setHeader($header)
  {
  }

  public function tlog($text)
  {
  }

  public function tlogData($text)
  {
  }

  public function tlogVariable($name, $variable)
  {
  }

  public function addChild($header = "")
  {
    return $this;
    // TODO(ppershing): is returning $this really safe?
    // Someone may try to compare instances of childs for
    // example.
    // return new NullTrace();
  }
}
