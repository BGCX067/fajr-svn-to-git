<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides support for logging debug information into
 * tree structure called trace.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\base;

/**
 * Interface to objects tracing debug information.
 *
 * You can pass this object along your call tree
 * and log important information alogn with it.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface Trace
{
  /**
   * Set the header of the current trace.
   *
   * @param string $header Header.
   *
   * @returns void
   */
  public function setHeader($header);

  /**
   * Append a new text node into tracer.
   *
   * @param string $text Text to append.
   *
   * @returns void
   */
  public function tlog($text);

  /**
   * Append a new data node into tracer. Data nodes are usually bigger than
   * text nodes and contains data that should retain formatting.
   *
   * @param string $string_data Data to append.
   *
   * @returns void
   */
  public function tlogData($string_data);

  /**
   * Convenient way of appending any variable definition to tracer.
   * Implementations should use var_export() or similar function to
   * obtain string version of variable.
   *
   * @param string $name     name of the variable to be dumped
   * @param mixed  $variable variable to be dumped
   *
   * @returns void
   */
  public function tlogVariable($name, $variable);

  /**
   * Create a new child node of this trace object and return it.
   *
   * @param string $header Optional child header
   *
   * @returns Trace Newly created child node.
   */
  public function addChild($header = "");
}
