<?php
/* {{{
 Copyright (c) 2010 Martin Králik
 Copyright (c) 2010 Martin Sucha

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

class AIS2ErrorCheckingConnection implements AIS2Connection {

	private $delegate = null;
	const INTERNAL_ERROR_PATTERN = '@^function main\(\) { (?:alert|webui\.onAppClosedOnServer)\(\'([^\']*)\'\);? }$@m';
	const APACHE_ERROR_PATTERN = '@Apache Tomcat.*<pre>([^<]*)</pre>@m';

	function __construct($delegate) {
		$this->delegate = $delegate;
	}

	public function get($url) {
		return $this->check($url, $this->delegate->get($url));
	}

	public function post($url, $data) {
		return $this->check($url, $this->delegate->post($url, $data));
	}

	public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
		return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
	}

	public function clearCookies() {
		return $this->delegate->clearCookies();
	}

	private function check($url, $response) {
		$matches = array();
		if (preg_match(self::INTERNAL_ERROR_PATTERN, $response, $matches))
		{
			throw new Exception('<b>Nastala chyba pri requeste.</b><br/>Zdôvodnenie od AISu: '.hescape($matches[1]).
                          '<br/>Požadovaná url: '.hescape($url));
    }
    if (preg_match(self::APACHE_ERROR_PATTERN, $response, $matches)) {
      throw new Exception('<b>Nastala chyba pri requeste.</b><br/>Zdôvodnenie od AISu: '.nl2br(hescape($matches[1])).
                          '<br/>Požadovaná url: '.hescape($url));
    }
		return $response;
	}




}
