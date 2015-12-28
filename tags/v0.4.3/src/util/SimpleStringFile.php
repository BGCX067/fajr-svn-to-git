<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains implementation of in-memory File
 *
 * @package    Fajr
 * @subpackage Fajr_Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
namespace fajr\util;

use fajr\libfajr\base\Preconditions;
use Exception;

/**
 * A File that uses string as file contents
 */
class SimpleStringFile implements File {

  private $s = '';
  private $closed = false;

  public function close()
  {
    $this->closed = true;
  }

  private function checkClosed() {
    if ($this->closed) {
      throw new IllegalStateException("File already closed");
    }
  }

  public function flush()
  {
    $this->checkClosed();
  }

  public function read($bytes)
  {
    $this->checkClosed();
    throw new Exception("Not implemented yet");
  }

  public function write($string)
  {
    Preconditions::checkIsString($string, '$string must be string');
    $this->checkClosed();
    $this->s .= $string;
  }

  public function getString()
  {
    return $this->s;
  }
}