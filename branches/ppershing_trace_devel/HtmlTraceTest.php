<?php
/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use \fajr\HtmlTrace;
/**
 * @ignore
 */
class HtmlTraceTest extends PHPUnit_Framework_TestCase
{
  private function newTimer() {
    return $this->getMock('\fajr\libfajr\base\Timer', array('getElapsedTime', 'reset'));
  }

  public function testRecursiveOutput()
  {
    $timer = $this->newTimer();
    $root = new HtmlTrace($timer, "ROOT_H");
    $other = new HtmlTrace($timer, "OTHER");
    $root->tlog("ROOT_TEXT");
    $c1 = $root->addChild("C1_H");
    $c2 = $root->addChild("C2_H");
    $cc = $c1->addChild("CC_H");
    $cc->tlog("CC_TEXT");

    $root_html = $root->getHtml();
    $c1_html = $c1->getHtml();
    // all texts and headers should appear in the output
    $this->assertRegExp("@ROOT_H@", $root_html);
    $this->assertRegExp("@ROOT_TEXT@", $root_html);
    $this->assertRegExp("@C1_H@", $root_html);
    $this->assertRegExp("@C2_H@", $root_html);
    $this->assertRegExp("@CC_H@", $root_html);
    $this->assertRegExp("@CC_TEXT@", $root_html);

    //Asserthing NotRegExp is quite tricky as some lines of this file
    //are included in the trace itself (CodeSnippet feature)
    // We need to watch about @ $ &quot; ]
    $this->assertNotRegExp("@[^$;\@\]]OTHER@", $root_html);
    // root nodes shoudn't be shown in child
    $this->assertNotRegExp("@[^$;\@\]]ROOT_H@", $c1_html);
    $this->assertNotRegExp("@[^$;\@\]]ROOT_TEXT@", $c1_html);
    $this->assertRegExp("@C1_H@", $c1_html);
  }

  public function testCallerData() {
    $data = HtmlTrace::getCallerData(0);
    $this->assertEquals(__CLASS__, $data['class']);
    $this->assertEquals(__FUNCTION__, $data['function']);
  }

  public function testVariableOutput() {
    $timer = $this->newTimer();
    $trace = new HtmlTrace($timer, "root");
    $trace->tlogVariable("var1", false);
    $trace->tlogVariable("var2", 4.7);
    $trace->tlogVariable("var3", array('str'));
    $trace->tlogVariable("var4", null);
    $html = $trace->getHtml();
    $this->assertRegExp("@false@", $html);
    $this->assertRegExp("@4.7@", $html);
    $this->assertRegExp("@array.*str@s", $html);
    $this->assertRegExp("@null@i", $html);

    $trace = new HtmlTrace($timer, "root");
    $trace->tlogVariable("var", "a'b");
    $html = $trace->getHtml();
    $this->assertRegExp("@a&#039;b@", $html);
  }

  public function testEscaping() {
    $timer = $this->newTimer();
    $trace = new HtmlTrace($timer);
    $trace->tlog("<&x");
    $trace->tlogData("&x>");
    $trace->tlogVariable("a'a", "a\"a");
    $html = $trace->getHtml();
    $this->assertNotRegExp("@&x@", $html);
    $this->assertNotRegExp("@&x@", $html);
    $this->assertNotRegExp("@a'a@", $html);
    $this->assertNotRegExp("@a\"a@", $html);
    $this->assertRegExp("@&lt;@", $html);
    $this->assertRegExp("@&gt;@", $html);
    $this->assertRegExp("@&amp;@", $html);
    $this->assertRegExp("@a&#039;a@", $html);
    $this->assertRegExp("@a&quot;a@", $html);
  }

}

?>
