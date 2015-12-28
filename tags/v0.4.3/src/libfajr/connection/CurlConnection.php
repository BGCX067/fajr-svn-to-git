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

  private $curl = null;
  private $cookieFile = null;
  private $options = null;

  public function  __construct(array $options, $cookieFile)
  {
    Preconditions::checkIsString($cookieFile, '$cookieFile should be string');
    $this->options = $options;
    $this->cookieFile = $cookieFile;
    $this->_curlInit();
  }

  public function __destruct()
  {
    curl_close($this->curl);
  }

  public function _curlInit()
  {
    $ch = curl_init();
    foreach ($this->options as $option=>$value) {
      curl_setopt($ch, $option, $value);
    }
    // do not put http response header in result
    curl_setopt($ch, CURLOPT_HEADER, false);
    // return response instead of echoing it to output
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);

    $this->curl = $ch;
  }

  public function get(Trace $trace, $url)
  {
    $trace->tlog("Http GET");
    $trace->tlogVariable("URL", $url);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_HTTPGET, true);
    return $this->exec($trace);
  }

  public function post(Trace $trace, $url, $data)
  {
    $trace->tlog("Http POST");
    $trace->tlogVariable("URL", $url);
    $child=$trace->addChild("POST data");
    $child->tlogVariable("post_data", $data);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_POST, true);

    $newPost = '';
    foreach ($data as $key => $value) {
      $newPost .= urlencode($key).'='.urlencode($value).'&';
    }
    $post = substr($newPost, 0, -1);

    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);

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

  private function exec(Trace $trace)
  {
    // read cookie file
    curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);

    $output = curl_exec($this->curl);
    $child = $trace->addChild("Response");
    $child->tlogVariable("Http resonse code",
        curl_getinfo($this->curl, CURLINFO_HTTP_CODE));
    $child->tlogVariable("Http content type",
        curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE));
    $child->tlogVariable("Response", $output);
    if (curl_errno($this->curl)) {
      $child->tlog("There was an error receiving data");
      throw new Exception("Chyba pri nadväzovaní spojenia:".
          curl_error($this->curl));
    }
    // Do not forget to save current file content
    curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);

    return $output;
  }

}
