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
use sfStorage;

/**
 * Equivalent of ScreenRequestExecutor.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FakeRequestExecutor extends DisableEvilCallsObject
{
  /** @var array default options which are added to each call */
  private $options;

  /** @var sfStorage data storage */
  private $storage;

  /**
   * Konštruktor.
   *
   */
  public function __construct(sfStorage $storage, array $options)
  {
    $this->options = $options;
    $this->storage = $storage;
  }

  public function spawnChild(array $options)
  {
    return new FakeRequestExecutor($this->storage,
                                   array_merge($this->options, $options));
  }

  /** Allowed characters for option keys/values and table name. */
  const ALLOWED_CHARS_REGEX = '@^[a-zA-Z0-9._]*$@';

  public function getFullPath(array $options, $tableName)
  {
    Preconditions::checkIsString($tableName);
    Preconditions::check($tableName != '', "Table name must be non-empty");

    $path = '';
    $options = array_merge($this->options, $options);
    foreach ($options as $key=>$value)
    {
      $pathSegment = $key.$value;
      if (!preg_match(self::ALLOWED_CHARS_REGEX, $pathSegment)) {
        throw IllegalArgumentException('Invalid characters in options');
      }
      $path .= '/' . $pathSegment;
    }
    if (!preg_match(self::ALLOWED_CHARS_REGEX, $tableName)) {
      throw IllegalArgumentException('Invalid characters in tableName');
    }
    return $path .= '/' . $tableName;
  }

  public function readTable($options, $tableName)
  {
    $fullPath = $this->getFullPath($options, $tableName);
    $storedData = $this->storage->read($fullPath);
    if ($storedData !== null) {
      return $storedData;
    }
    return array();
  }

  public function writeTable($options, $tableName, array $data)
  {
    $fullPath = $this->getFullPath($options, $tableName);
    $this->storage->write($fullPath, $data);
  }

}
?>
