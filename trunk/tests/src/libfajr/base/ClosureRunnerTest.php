<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\base\ClosureRunner;

class Data
{
  public $arg1;
}

/**
 * @ignore
 */
class ClosureTest extends PHPUnit_Framework_TestCase
{
  protected $backupGlobals = false;

  public function testArguments()
  {
    $data = new Data();
    $data->arg1 = false;

    $f = function($data)
    {
      $data->arg1 = true;
    };

    $closure = new ClosureRunner($f, $data);
    // Stupid PHP do not have nested variable scopes, simulate destruction.
    unset($closure);

    $this->assertTrue($data->arg1);
  }

}
