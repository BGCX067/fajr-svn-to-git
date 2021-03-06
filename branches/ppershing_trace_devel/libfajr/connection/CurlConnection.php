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

/**
 * Provides wrapper for Curl library.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty@gjh.sk>
 * @author     Martin Kralik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\base\Trace;
use fajr\libfajr\base\ClosureRunner;
use \Exception;
/**
 * Provides HttpConnection wrapper for Curl library.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Martin Sucha <anty@gjh.sk>
 * @author     Martin Kralik <majak47@gmail.com>
 */
class CurlConnection implements HttpConnection {

  const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7';

  private $curl = null;
  private $cookieFile = null;
  private $userAgent = null;


  public function  __construct($cookieFile, $userAgent = null) {
    $this->userAgent = $userAgent ? $userAgent: self::USER_AGENT;

    $this->cookieFile = $cookieFile;
    $this->_curlInit();
  }

  public function __destruct() {
    curl_close($this->curl);
  }

  public function _curlInit() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FORBID_REUSE, false); // Keepalive konekcie
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);

    $this->curl = $ch;
  }

  public function get(Trace $trace, $url) {
    $trace->tlog("Http GET");
    $trace->tlogVariable("URL", $url);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_HTTPGET, true);
    return $this->exec($trace);
  }

  public function post(Trace $trace, $url, $data) {
    $trace->tlog("Http POST");
    $trace->tlogVariable("URL", $url);
    $child=$trace->addChild("POST data");
    $child->tlogVariable("post_data", $data);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_POST, true);

    $newPost = '';
    foreach ($data as $key => $value) $newPost .= urlencode($key).'='.urlencode($value).'&';
    $post = substr($newPost, 0, -1);

    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);

    return $this->exec($trace);
  }

  public function addCookie($name, $value, $expire, $path, $domain,
                $secure = true, $tailmatch = false) {
    // Closing+reopening handle seems to be the only way how to force save/reload
    // of cookies. We loose reusable connection though.
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

  public function clearCookies() {
    // Closing+reopening handle seems to be the only way how to force save/reload
    // of cookies. We loose reusable connection though.
    curl_close($this->curl);
    @unlink($this->cookieFile);
    $this->_curlInit();
  }

  private function exec(Trace $trace) {
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
