<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\base\Trace;

class GzipDecompressingConnection implements HttpConnection
{
  /**
   * temporary directory for output files.
   * Deprecate when PHP 6 is available!
   */
  private $tempDir = null;

  /**
   * @var HttpConnection Delegate connection over which we are working.
   */
  private $delegate = null;

  function __construct(HttpConnection $delegate, $tempDir)
  {
    $this->delegate = $delegate;
    $this->tempDir = $tempDir;
  }

  /**
   * GET request. @see HttpConnection::get
   *
   * @param string $url URL to get
   *
   * @returns string (decompressed) content retrieved from $url
   */
  public function get(Trace $trace, $url)
  {
    return $this->decompressIfGzip($trace,
                                   $this->delegate->get($trace, $url));
  }

  /**
   * POST request. @see HttpConnection::post
   *
   * @param string $url URL to get
   * @param array  $data post data
   *
   * @returns string (decompressed) content retrieved from $url
   */
  public function post(Trace $trace, $url, $data)
  {
    return $this->decompressIfGzip($trace,
                                   $this->delegate->post($trace, $url, $data));
  }

  //TODO(ppershing): refactor these values into Cookie class
  public function addCookie($name, $value, $expire, $path,
                            $domain, $secure = true, $tailmatch = false)
  {
    return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
  }

  public function clearCookies()
  {
    return $this->delegate->clearCookies();
  }

  const GZIP_HEADER = "\x1f\x8b\x08\x00\x00\x00\x00\x00";

  private function decompressIfGzip(Trace $trace, $response)
  {
    if (strlen($response) >= 8 &&
        substr_compare($response, self::GZIP_HEADER, 0, 8) === 0) {
      $child = $trace->addChild("Content is gzipped, decompressing...");
      $gzippedTempFile = tempnam($this->tempDir, 'gzip');
      @file_put_contents($gzippedTempFile, $response);
      ob_start();
      readgzfile($gzippedTempFile);
      $decoded = ob_get_clean();
      @unlink($gzippedTempFile);
      // USE THIS IN PHP6: $decoded = gzdecode($response);
      $child->tlogVariable("Gzip decoded response", $decoded);
      return $decoded;
    } else {
      return $response;
    }
  }
}
