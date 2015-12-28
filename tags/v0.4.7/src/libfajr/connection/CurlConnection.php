<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides wrapper for Curl library.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\base\ClosureRunner;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\connection\HttpConnection;
use Exception;

/**
 * Provides HttpConnection wrapper for Curl library.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Martin Kralik <majak47@gmail.com>
 */
class CurlConnection implements HttpConnection
{

  /** @var curl_handle */
  private $curl = null;

  /** @var string */
  private $cookieFile = null;

  /** @var array */
  private $options = null;

  /**
   * @var RequestStatisticsImpl
   */
  private $stats = null;

  public function  __construct(array $options, $cookieFile)
  {
    Preconditions::checkIsString($cookieFile, '$cookieFile should be string');
    $this->options = $options;
    $this->cookieFile = $cookieFile;
    $this->_curlInit();
    $this->stats = new RequestStatisticsImpl();
  }

  public function __destruct()
  {
    curl_close($this->curl);
  }

  private function _curlSetOption($option, $value)
  {
    if (!curl_setopt($this->curl, $option, $value)) {
      throw new Exception('Failed to set CURL option ' . $option);
    }
  }

  public function _curlInit()
  {
    $ch = curl_init();
    if ($ch === false) {
      throw new Exception('Cannot create CURL handle');
    }

    $this->curl = $ch;

    foreach ($this->options as $option=>$value) {
      $this->_curlSetOption($option, $value);
    }
    // do not put http response header in result
    $this->_curlSetOption(CURLOPT_HEADER, false);
    // return response instead of echoing it to output
    $this->_curlSetOption(CURLOPT_RETURNTRANSFER, true);
    $this->_curlSetOption(CURLOPT_COOKIEFILE, $this->cookieFile);
    $this->_curlSetOption(CURLOPT_COOKIEJAR, $this->cookieFile);
  }

  public function get(Trace $trace, $url)
  {
    $trace->tlog("Http GET");
    $trace->tlogVariable("URL", $url);
    $this->_curlSetOption(CURLOPT_URL, $url);
    $this->_curlSetOption(CURLOPT_HTTPGET, true);
    return $this->exec($trace);
  }

  public function post(Trace $trace, $url, $data)
  {
    $trace->tlog("Http POST");
    $trace->tlogVariable("URL", $url);
    $child=$trace->addChild("POST data");
    $child->tlogVariable("post_data", $data);
    $this->_curlSetOption(CURLOPT_URL, $url);
    $this->_curlSetOption(CURLOPT_POST, true);

    $newPost = '';
    foreach ($data as $key => $value) {
      $newPost .= urlencode($key).'='.urlencode($value).'&';
    }
    $post = substr($newPost, 0, -1);

    $this->_curlSetOption(CURLOPT_POSTFIELDS, $post);

    return $this->exec($trace);
  }

  public function addCookie($name, $value, $expire, $path, $domain,
      $secure = true, $tailmatch = false)
  {
    // Closing+reopening handle seems to be the only way how to force save/reload
    // of cookies. We lose reusable connection though.
    $closureRunner = new ClosureRunner(array($this, '_curlInit'));
    curl_close($this->curl);

    $fh = fopen($this->cookieFile, 'a');
    if (!$fh) {
      throw new Exception('Neviem otvoriť súbor s cookies.');
    }

    $cookieLine = $domain."\t".($tailmatch?'TRUE':'FALSE')."\t";
    $cookieLine .= $path."\t".($secure?'TRUE':'FALSE')."\t";
    $cookieLine .= $expire."\t".$name."\t".str_replace(' ', '+',$value);
    $cookieLine .= "\n";

    if (fwrite($fh, $cookieLine) < strlen($cookieLine)) {
      throw new Exception('Failed to add cookies.');
    }
    if (!fclose($fh)) {
      throw new Exception('Failed to add cookies.');
    };
  }

  public function clearCookies()
  {
    // Closing+reopening handle seems to be the only way how to force save/reload
    // of cookies. We lose reusable connection though.
    curl_close($this->curl);
    @unlink($this->cookieFile);
    $this->_curlInit();
  }

  private function processStatistics($curl)
  {
    $size = curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD) +
            curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $time = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
    $errors = curl_errno($curl) ? 1 : 0;
    $this->stats->addStats($errors, $size, $time);
  }

  private function exec(Trace $trace)
  {
    // read cookie file
    $this->_curlSetOption(CURLOPT_COOKIEFILE, $this->cookieFile);

    $output = curl_exec($this->curl);
    $child = $trace->addChild("Response");
    $child->tlogVariable("Http resonse code",
        curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
    $child->tlogVariable("Http content type",
        curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE));
    $child->tlogVariable("Response", $output);

    $this->processStatistics($this->curl);

    if (curl_errno($this->curl)) {
      $child->tlog("There was an error receiving data");
      throw new Exception("Chyba pri nadväzovaní spojenia:".
          curl_error($this->curl));
    };

    // Do not forget to save current file content
    $this->_curlSetOption(CURLOPT_COOKIEJAR, $this->cookieFile);

    return $output;
  }

  /**
   * @returns RequestStatistics
   */
  public function getStats()
  {
    return $this->stats;
  }
}
