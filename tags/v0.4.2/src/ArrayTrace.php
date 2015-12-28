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

/**
 * A Trace that stores its data in an array
 */
class ArrayTrace implements Trace
{
  private $header;
  private $children = array();
  private $constructTime = null;
  private $timer = null;

  /**
   * Construct an ArrayTrace
   * @param Timer $timer timer to measure time with
   * @param string $header header text to be displayed
   */
  public function __construct(Timer $timer, $header = "")
  {
    $this->setHeader($header);
    $this->constructTime = microtime(true);
    $this->timer = $timer;
  }


  /**
   * Set this trace's header
   * @param string $header header text to be displayed
   */
  public function setHeader($header)
  {
    Preconditions::checkIsString($header, 'header');
    $this->header = $header;
  }

  /**
   * Log an event
   * @param string $text text to be displayed
   */
  public function tlog($text)
  {
    Preconditions::checkIsString($text, 'text');
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'log',
                              'data'=>$text);
  }

  /**
   * Log data
   * @param string $text text to be displayed as is
   */
  public function tlogData($text)
  {
    Preconditions::checkIsString($text, 'text');
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'data',
                              'data'=>$text);
  }

  /**
   * Log contents of a variable
   * @param string $name name of the variable (without dollar sign)
   * @param mixed $variable contents of the variable to be dumped
   */
  public function tlogVariable($name, $variable)
  {
    $data = preg_replace("@\\\\'@", "'", var_export($variable, true));
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'variable',
                              'name'=>$name,
                              'data'=>$data);
  }

  /**
   * Create a new ArrayTrace at the insertion point
   * @param string $header text to use as header
   * @return ArrayTrace child trace object
   */
  public function addChild($header = "")
  {
    Preconditions::checkIsString($header, 'header');
    $child = new ArrayTrace($this->timer, $header);
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'trace',
                              'trace'=>$child);
    return $child;
  }

  /**
   * Finds appropriate caller data associated with stacktrace.
   *
   * @param int $depth How much back in stack we should go.
   *                   Zero defaults to caller of this function.
   *
   * @returns array @see debug_backtrace for details
   */
  public static function getCallerData($depth) {
    $data = debug_backtrace();
    for ($i = 0; $i < $depth + 1; $i++) {
      array_shift($data);
    }
    $caller = array_shift($data);
    return $caller;
  }

  /**
   * Returns information about this particular trace event.
   *
   * @returns array with with time, caller data and code snippet
   */
  private function getInfoArray() {
    $info = array();

    $caller = $this->getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = $caller['function'];

    $caller = $this->getCallerData(1);
    $file = $caller['file'];
    $line = $caller['line'];
    $snippet = CodeSnippet::getCodeSnippet($file, $line, 5);

    $info['class'] = $class;
    $info['function'] = $function;
    $info['line'] = $line;
    $info['file'] = $file;
    $info['snippet'] = $snippet;
    $info['elapsedTime'] = $this->timer->getElapsedTime();

    return $info;
  }

  /**
   * Return this trace's children as an array
   * @return array(array('type'=>..., ...)) children information
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Return this trace's header text
   * @return string header text
   */
  public function getHeader()
  {
    return $this->header;
  }
  
}
