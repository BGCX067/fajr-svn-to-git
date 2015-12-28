<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Regression__Fake_data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

namespace fajr\regression\fake_data;

use PHPUnit_Framework_TestCase;
use RecursiveDirectoryIterator;
use RegexIterator;
use RecursiveIteratorIterator;
use fajr\libfajr\storage\FileStorage;

/**
 * @ignore
 */
require_once 'test_include.php';
/**
 * @ignore
 */
class FakeDataTest extends PHPUnit_Framework_TestCase
{

  /**
   * Ensure that all fake data we use are valid FileStorage
   * data files. This should ensure, that fake backend and
   * other services depending on fake data will not encounter
   * broken data.
   */
  public function testAllData()
  {
    $storage = new FileStorage(array("root_path" => "/"));

    $dir = fake_data::getDirectory();
    $it = new RecursiveDirectoryIterator($dir);
    $nonrecursiveIt = new RecursiveIteratorIterator($it);
    $regexIt = new RegexIterator($nonrecursiveIt, '@\.dat$@');
    foreach ($regexIt as $file) {
      $this->assertTrue($file->isFile());
      $key = $file->getPathname();
      $key = preg_replace("@\.dat$@", "", $key);

      $data = $storage->read($key);
      $this->assertTrue(null !== $data);
    }
  }


}
