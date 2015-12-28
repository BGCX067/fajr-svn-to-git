<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * AIS2 Version information.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

/**
 * AIS2 Version information.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2Version {
  /** @var int AIS version number*/
  private $ais;

  /** @var int major version number*/
  private $major;

  /** @var int minor version number*/
  private $minor;

  /** @var int patch version number*/
  private $patch;

  public function __construct($ais, $major, $minor, $patch)
  {
    $this->ais = $ais;
    $this->major = $major;
    $this->minor = $minor;
    $this->patch = $patch;
  }

  public function getAis()
  {
    return $this->ais;
  }

  public function getMajor()
  {
    return $this->major;
  }

  public function getMinor()
  {
    return $this->minor;
  }

  public function getPatch()
  {
    return $this->patch;
  }


  public function toString()
  {
    return $this->ais . '.' . $this->major . '.' .
      $this->minor . '.' . $this->patch;
  }

  /**
   * Compare two versions
   *
   * @param AIS2Version $other object to compare to
   *
   * @returns int -1,0,1 if current object is less than,
   *   equal and greather than $other
   */
  public function compareTo(AIS2Version $other)
  {
    if ($this->ais < $other->ais) return -1;
    if ($this->ais > $other->ais) return 1;
    if ($this->major < $other->major) return -1;
    if ($this->major > $other->major) return 1;
    if ($this->minor < $other->minor) return -1;
    if ($this->minor > $other->minor) return 1;
    if ($this->patch < $other->patch) return -1;
    if ($this->patch > $other->patch) return 1;
    return 0;
  }
}
