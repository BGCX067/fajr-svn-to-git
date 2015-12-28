<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains equivalent of ScreenRequestExecutor
 * for fake screens.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\fake;
use fajr\libfajr\pub\base\Trace;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\SimpleConnection;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\util\StrUtil;

/**
 * Equivalent of ScreenRequestExecutor.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FakeRequestExecutor extends DisableEvilCallsObject
{
  private $rootPath;
  private $options;

  /**
   * Konštruktor.
   *
   */
  public function __construct($rootPath, array $options)
  {
    Preconditions::checkIsString($rootPath);
    $this->rootPath = $rootPath;
    $this->options = $options;
  }

  public function spawnChild(array $options)
  {
    return new FakeRequestExecutor($this->rootPath, array_merge($this->options, $options));
  }

  public function getFullPath($rootPath, array $options, $tableName)
  {
    $path = $rootPath;
    $options = array_merge($this->options, $options);
    foreach ($options as $key=>$value)
    {
      $pathSegment = $key.$value;
      if (!preg_match('@^[a-zA-Z0-9.]*$@', $pathSegment)) {
        throw IllegalArgumentException();
      }
      $path .= '/' . $pathSegment;
    }
    if (!preg_match('@^[a-zA-Z0-9.]*$@', $tableName)) {
      throw IllegalArgumentException();
    }
    return $path .= '/' . $tableName . '.dat';
  }

  public function readTable($options, $tableName)
  {
   $fullPath = $this->getFullPath($this->rootPath, $options, $tableName);
    if (!(file_exists($fullPath))) {
      throw new Exception("Cannot find file '$fullPath'.");
    }
    return include "$fullPath";
  }

}
?>
