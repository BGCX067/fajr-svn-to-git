<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Class that initializes all session-related values and starts session.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

/**
 * Helper that helps initializing and starting session.
 *
 * Class provides functionality to start session with custom settings
 * like lifetime, path where session files are stored, etc.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class SessionInitializer
{
  /** @var int Life time of session in seconds */
  private $lifeTimeSec;

  /** @var string Path where session files are stored */
  private $savePath;

  /** @var string Session cookie path */
  private $path;

  /** @var string Session cookie domain */
  private $domain;

  /**
   * Constructor
   *
   * @param int lifeTimeSec life time of session in seconds
   * @param string savePath path where session files are stored
   * @param string path     cookie path
   * @param string domain   cookie domain
   */
  public function __construct($lifeTimeSec, $savePath, $path, $domain)
  {
    $this->lifeTimeSec = $lifeTimeSec;
    $this->savePath = $savePath;
    $this->path = $path;
    $this->domain = $domain;
  }

  /**
   * Starts the session.
   *
   * Setup all neccessary setting of the session (according to constructor values)
   * and start the session.
   *
   * @returns void
   */
  public function startSession()
  {
    session_cache_expire($this->lifeTimeSec / 60);
    session_set_cookie_params($this->lifeTimeSec,
                              $this->path,
                              $this->domain);
    // cache expire, server
    ini_set("session.gc_maxlifetime", $this->lifeTimeSec);
    ini_set("session.cookie_lifetime", $this->lifeTimeSec);
    // custom cache expire is possible only for custom session directory
    session_save_path($this->savePath);
    session_start();
  }
}
