<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Sucha <anty.sk@gmail.com>
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 */

namespace fajr;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\Timer;
use fajr\libfajr\util\CodeSnippet;
use fajr\libfajr\base\Preconditions;
use InvalidArgumentException;
use fajr\util\File;
use fajr\util\TraceUtil;

/**
 * A Trace that writes trace information to a file
 */
class FileTrace implements Trace
{
  private $constructTime = null;
  private $timer = null;

  /** @var File file to write to */
  private $file = null;

  /** @var int Number of tabs to indent */
  private $indentLevel = 0;

  /**
   * Construct a FileTrace
   * @param Timer $timer timer to measure time with
   * @param resource|string $file file resource handle or filename to write to
   * @param string $header header text to be displayed
   */
  public function __construct(Timer $timer, File $file, $indentLevel = 0, $header = "")
  {
    $this->file = $file;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
    $this->indentLevel = $indentLevel;
    $this->writeIndent();
    $this->file->write($header . "\n");
    $this->writeInfoLine("Type: Trace\n");
    $this->writeInfo();
    $this->file->flush();
    $this->indentLevel += 1;
  }

  /**
   * FileTrace does not support setting header text after construction.
   * TODO(anty): implement when needed
   * @param string $header header text to be displayed
   */
  public function setHeader($header)
  {
    Preconditions::checkIsString($header, '$header should be string');
  }

  /**
   * Log an event
   * @param string $text text to be displayed
   */
  public function tlog($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeIndent();
    $this->file->write($text . "\n");
    $this->writeInfoLine("Type: log\n");
    $this->writeInfo();
    $this->file->flush();
  }

  /**
   * Log data
   * @param string $text text to be displayed as is
   */
  public function tlogData($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeIndent();
    $this->file->write($text . "\n");
    $this->writeInfoLine("Type: data\n");
    $this->writeInfo();
    $this->file->flush();
  }

  /**
   * Log contents of a variable
   * @param string $name name of the variable (without dollar sign)
   * @param mixed $variable contents of the variable to be dumped
   */
  public function tlogVariable($name, $variable)
  {
    $data = preg_replace("@\\\\'@", "'", var_export($variable, true));
    
    $this->writeIndent();
    $this->file->write($name . ' = ' . $data. "\n");
    $this->writeInfoLine("Type: variable\n");
    $this->writeInfo();
    $this->file->flush();
  }

  /**
   * Create a new FileTrace
   * @param string $header text to use as header
   * @returns FileTrace child trace object
   */
  public function addChild($header = "")
  {
    Preconditions::checkIsString($header, '$header should be string');
    return new FileTrace($this->timer, $this->file,
                         $this->indentLevel + 1, $header);
  }

  /**
   * Writes information about current Trace event
   *
   */
  private function writeInfo() {
    $caller = TraceUtil::getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "N/A";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = isset($caller['function']) ? $caller['function'] : 'N/A';

    $caller = TraceUtil::getCallerData(1);
    $file = isset($caller['file']) ? $caller['file'] : 'N/A';
    $line = isset($caller['line']) ? $caller['line'] : 'N/A';

    $this->writeInfoLine("Time: " . $this->timer->getElapsedTime() .
                          ' (' . date('d.M.Y H:i:s') . ')');
    $this->writeInfoLine("Class: " . $class);
    $this->writeInfoLine("Function: " . $function);
    $this->writeInfoLine("File: " . $file);
    $this->writeInfoLine("Line: " . $line);
  }

  private function writeIndent()
  {
    $this->file->write(str_repeat("\t", $this->indentLevel));
  }

  private function writeInfoLine($text)
  {
    Preconditions::checkIsString($text, 'text must be string');
    $this->writeIndent();
    $this->file->write("\t" . $text . "\n");
  }
  
}
