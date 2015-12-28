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

class AIS2CurlConnection implements AIS2Connection {

	const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7';

	private $curl = null;
	private $cookieFile = null;

	public function  __construct($cookieFile, $userAgent = null) {
		if ($userAgent === null) {
			$userAgent = self::USER_AGENT;
		}

		$this->cookieFile = $cookieFile;

		$ch = curl_init(); // prvy krat inicializujeme curl
		curl_setopt($ch, CURLOPT_FORBID_REUSE, false); // Keepalive konekcie
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);

		$this->curl = $ch;
	}

	public function get($url) {
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HTTPGET, true);

		return $this->exec();
	}

	public function post($url, $data) {
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST, true);

		$newPost = '';
		foreach ($data as $key => $value) $newPost .= urlencode($key).'='.urlencode($value).'&';
		$post = substr($newPost, 0, -1);

		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);

		return $this->exec();
	}

	public function addCookie($name, $value, $expire, $path, $domain,
								$secure = true, $tailmatch = false) {
		$fh = fopen($this->cookieFile, 'a');
		if (!$fh) throw new Exception('Neviem otvoriť súbor s cookies.');

		$cookieLine = $domain."\t".($tailmatch?'TRUE':'FALSE')."\t";
		$cookieLine .= $path."\t".($secure?'TRUE':'FALSE')."\t";
		$cookieLine .= $expire."\t".$name."\t".str_replace(' ', '+',$value);
		$cookieLine .= "\n";

		fwrite($fh, $cookieLine);
		fclose($fh);
	}

	public function clearCookies() {
		unlink($this->cookieFile);
	}

	private function exec() {
		$output = curl_exec($this->curl);
		if (curl_errno($this->curl)) {
			throw new Exception("Chyba pri nadväzovaní spojenia:".
					curl_error($this->curl));
		}

		return $output;
	}

}