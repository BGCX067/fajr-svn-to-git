<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains implementation of File
 *
 * @package    Fajr
 * @subpackage Fajr_Util
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
namespace fajr\util;

use fajr\libfajr\base\Preconditions;
use Exception;

/**
 * A File that uses PHP's file functions to write to real files or PHP streams
 *
 * Users of this class are advised to close the file explicitly, as
 * close errors in destructor are silently ignored.
 */
class PHPFile implements File {

  private $handle;

  public function __construct($path, $mode)
  {
    Preconditions::checkIsString($path, 'path must be string');
    Preconditions::checkIsString($mode, 'mode must be string');
    $this->handle = @fopen($path, $mode);
    if ($this->handle === false) {
      throw new Exception('Cannot open file');
    }
  }

  public function close()
  {
    if ($this->handle === null) {
      return;
    }
    if (!@fclose($this->handle)) {
      throw new Exception('Cannot close file');
    }
    $this->handle = null;
  }

  private function checkNotClosed()
  {
    if ($this->handle === null) {
      throw new IllegalStateException("This file was already closed");
    }
  }

  public function flush()
  {
    $this->checkNotClosed();
    if (!@fflush($this->handle)) {
      throw new Exception("Cannot flush file");
    }
  }

  public function read($bytes)
  {
    $this->checkNotClosed();
    $data = @fread($this->handle, $bytes);
    // fread should return '' on EOF
    // contrary to the PHP documentation (bug in PHP doc?)
    // see http://www.php.net/manual/en/function.fread.php#95413
    if (data === false) {
      throw new Exception("Cannot read file");
    }
    return $data;
  }

  public function write($string)
  {
    Preconditions::checkIsString($string, '$string must be string');
    $this->checkNotClosed();
    if (@fwrite($this->handle, $string) === false) {
      throw new Exception("Cannot write file");
    }
  }

  /**
   * Destruct this file and close the underlying handle if possible.
   *
   * Note that the close operation may silently fail in this case,
   * possibly leading to loss of data. Users of this class are advised to
   * close the file explicitly.
   */
  public function __destruct()
  {
    // Don't call close here as we don't want to throw exceptions from
    // destructor!
    if ($this->handle !== null) {
      @fclose($this->handle);
    }
  }
}
