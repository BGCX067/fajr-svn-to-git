<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Storage
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\storage;

use \PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class Helper {
  public static $status = null;
  public $a;
  public $b;
  public function __construct($a, $b)
  {
    $this->a = $a;
    $this->b = $b;
  }

  public function __sleep()
  {
    self::$status = 'sleep';
    return array('a');
  }

  public function __wakeup()
  {
    assert(self::$status == 'sleep');
    self::$status = 'wakeup';
    $this->b = null;
  }
}

/**
 * @ignore
 */
class SerializerTest extends PHPUnit_Framework_TestCase
{
  // Global note: use assertSame instead of assertEquals ('===' vs '==')

  private function serializeDeserialize($value) {
    return Serializer::deserialize(Serializer::serialize($value));
  }

  public function testSerializeDeserializeZeroishValues()
  {
    $this->assertSame(null, $this->serializeDeserialize(null));
    $this->assertSame(0, $this->serializeDeserialize(0));
    $this->assertSame(false, $this->serializeDeserialize(false));
    $this->assertSame(array(), $this->serializeDeserialize(array()));
  }

  public function testSerializeDeserializeStandardValues()
  {
    $this->assertSame(47, $this->serializeDeserialize(47));
    $this->assertSame("str", $this->serializeDeserialize("str"));
    $this->assertSame(true, $this->serializeDeserialize(true));
    $this->assertSame(array("a"=>"b"), $this->serializeDeserialize(array("a"=>"b")));
  }

  public function testSerializeDeserializeObjects()
  {
    $helper = new Helper('x', 'y');
    $tmp = $this->serializeDeserialize($helper);
    $this->assertSame("wakeup", Helper::$status);
    $this->assertSame('y', $helper->b);
    $this->assertSame(null, $tmp->b);
  }

  public function testSerializeDeserializeCyclic()
  {
    $a = array('a');
    $b = array('b');
    $a['ref'] = &$b;
    $b['ref'] = &$a;
    $tmp = $this->serializeDeserialize($a);
    $this->assertSame('a', $tmp[0]);
    $this->assertSame('b', $tmp['ref'][0]);
    $this->assertSame('a', $tmp['ref']['ref'][0]);
    $this->assertSame('b', $tmp['ref']['ref']['ref'][0]);
  }
}
