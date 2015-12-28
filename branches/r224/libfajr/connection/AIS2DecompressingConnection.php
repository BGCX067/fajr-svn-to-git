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

class AIS2DecompressingConnection implements AIS2Connection {

	private $tempDir = null;
	private $delegate = null;

	function __construct(AIS2Connection $delegate, $tempDir) {
		$this->tempDir = $tempDir;
		$this->delegate = $delegate;
	}

	public function get($url) {
		return $this->decompress($this->delegate->get($url));
	}

	public function post($url, $data) {
		return $this->decompress($this->delegate->post($url, $data));
	}

	public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
		return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
	}

	public function clearCookies() {
		return $this->delegate->clearCookies();
	}

	private function decompress($response) {
		if (strlen($response) >= 8 && substr_compare($response, "\x1f\x8b\x08\x00\x00\x00\x00\x00",0,8) === 0) {
			$gzippedTempFile = tempnam($this->tempDir, 'gzip');
			@file_put_contents($gzippedTempFile, $response);
			ob_start();
			readgzfile($gzippedTempFile);
			$response = ob_get_clean();
			unlink($gzippedTempFile);
		}
		return $response;
	}



}