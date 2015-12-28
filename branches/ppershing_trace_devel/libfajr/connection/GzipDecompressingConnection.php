<?php
/* {{{
Copyright (c) 2010 Martin Sucha
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */
namespace fajr\libfajr\connection;

use fajr\libfajr\base\Trace;

class GzipDecompressingConnection implements HttpConnection {
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
  public function get(Trace $trace, $url) {
    return $this->decompressIfGzip($trace,
                                   $this->delegate->get($trace, $url));
  }

  /**
   * POST request. @see HttpConnection::post
   *
   * @param string $url URL to get
   * @param array $data post data
   *
   * @returns string (decompressed) content retrieved from $url
   */
  public function post(Trace $trace, $url, $data) {
    return $this->decompressIfGzip($trace,
                                $this->delegate->post($trace, $url, $data));
  }

  public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
    return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
  }

  public function clearCookies() {
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
