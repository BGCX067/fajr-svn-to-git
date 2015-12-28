<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\Timer;
use fajr\libfajr\util\CodeSnippet;
use fajr\htmlgen\Renderable;
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\Label;
// TODO(ppershing): Move to html templates when possible
// TODO(ppershing): Do not store html in children, instead
//                  render it on the fly.
class HtmlTrace implements Trace, Renderable
{
  private $header;
  private $children = array();
  private $constructTime = null;
  private $timer = null;

  public function __construct(Timer $timer, $header = "", $escape = true)
  {
    $this->header = $escape ? hescape($header) : $header;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
  }

  public function setHeader($header)
  {
    $this->header = $header;
  }

  public function tlog($text)
  {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getInfoString() . hescape($text)."</div>");
  }

  public function tlogData($text)
  {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getInfoString() . "<pre class='trace'>" .
        hescape($text)."</pre></div>");
  }

  public function tlogVariable($name, $variable)
  {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getInfoString() . "\$".hescape($name).":= <pre class='trace'>" .
        hescape(preg_replace("@\\\\'@", "'", var_export($variable, true))) . "</pre></div>");
  }

  public function addChild($header = "")
  {
    $child = new HtmlTrace($this->timer, $this->getInfoString().hescape($header), false);
    $this->children[] = $child;
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
  public static function getCallerData($depth)
  {
    $data = debug_backtrace();
    for ($i = 0; $i < $depth + 1; $i++) {
      array_shift($data);
    }
    $caller = array_shift($data);
    return $caller;
    return $caller['class']."::".$caller['function'].":";
  }

  /**
   * Returns html rendered information about this particular trace event.
   *
   * @returns string html string with time, caller data and code snippet
   */
  private function getInfoString()
  {
    $caller = $this->getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = $caller['function'];

    $caller = $this->getCallerData(1);
    $file = $caller['file'];
    $line = $caller['line'];
    $snippet = CodeSnippet::getCodeSnippet($file, $line, 5);
    $tooltipHtml = sprintf(
        "<span class='trace_tooltip'>Function&nbsp;%s::%s()<br/>\n".
        "Line:&nbsp;%s<br/>\n".
        "File:&nbsp'%s'<br/>\n".
        "<br/>Code snippet:<pre>%s</pre></span>",
        hescape($class), hescape($function), hescape($line), hescape($file),
        hescape($snippet));
    return sprintf("<span class='trace_s'>%+0.2fs %s</span>",
                   $this->timer->getElapsedTime(), $tooltipHtml);
  }

  /**
   * Returns html representation of trace tree rooted at this node.
   *
   * @returns string html
   */
  public function getHtml()
  {
    $html = "";
    foreach ($this->children as $child) {
      assert($child instanceof Renderable);
      $html .= $child->getHtml()."\n";
    }

    $collapsible = new Collapsible(new Label("<div>" . $this->header . "</div>"),
                                   new Label($html), true);
    return $collapsible->getHtml();
  }
}
