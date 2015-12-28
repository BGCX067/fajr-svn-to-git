<?php
/* {{{
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

interface AIS2Connection {

	/**
	 * Spravi get request vramci tohto spojenia
	 * @param string $url
	 */
	public function get($url);

	/**
	 * Spravi post request vramci tohto spojenia
	 * @param string $url
	 * @param array $data asociativne pole dat na poslanie
	 */
	public function post($url, $data);

	/**
	 * Pridá cookie do spojenia
	 * @param string $name Názov cookie
	 * @param string $value Hodnota cookie
	 * @param int $expire Unix timestamp, kedy expiruje (co znamena 0 treba este zistit)
	 * @param string $path Korenova cesta platnosti cookie. / znamena celu domenu
	 * @param string $domain Domena, kde cookie plati
	 * @param boolean $secure Ci je potrebne HTTPS na odovzdanie cookie
	 * @param boolean $tailmatch Ci mozu vsetky poddomeny dostat tuto cookie
	 */
	public function addCookie($name, $value, $expire, $path, $domain,
		$secure=true, $tailmatch=false);

	/**
	 * Vymaze vsetky cookie, pripadne aj ich asociovane ulozisko
	 */
	public function clearCookies();

}