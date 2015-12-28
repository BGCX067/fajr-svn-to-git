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
 * Trieda reprezentujúca prihlasovanie pomocou cosign
 *
 * @author majak, ms
 */
class AIS2CosignLogin extends AIS2AbstractLogin {

	const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
	const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';
	const MAIN_PAGE = 'https://ais2.uniba.sk';

	private $username = null;
	private $krbpwd = null;
	
	public function __construct($username, $krbpwd) {
		assert($username != null);
		assert($krbpwd != null);
		$this->username = $username;
		$this->krbpwd = $krbpwd;
	}

	public function login(AIS2Connection $connection) {
		$login = $this->username;
		$krbpwd = $this->krbpwd;

		// Username a password si nebudeme pamatat dlhsie ako treba
		$this->username = null;
		$this->krbpwd = null;

		$data = $connection->get(self::LOGIN);
		if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data)) {
			assert($login !== null && $krbpwd !== null);
			$data = $connection->post(self::COSIGN_LOGIN, array('ref' => self::LOGIN, 'login'=> $login, 'krbpwd' => $krbpwd));
			if (!preg_match('@\<base href\="https://ais2\.uniba\.sk/ais/portal/pages/portal_layout\.jsp"\>@', $data)) {
				if (preg_match('@Pri pokuse o prihlásenie sa vyskytol problém:@', $data)) {
					if ($reason = match($data, '@\<div style\="color:#FF0000;"\>\<b\>([^<]*)\<\/b\>@')) {
						throw new Exception('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
					}
				}
				if ($reason = match($data, '@\<title\>IIKS - Chyba pri prihlasovaní:([^<]*)\<\/title\>@')) {
					throw new Exception('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
				}
				throw new Exception('Nepodarilo sa prihlásiť, dôvod neznámy.');
			}
			$this->loggedIn = true;
			return true;
		}
		$this->loggedIn = true; // naozaj?
		return true;
	}

	public function logout(AIS2Connection $connection) {
		$connection->post(self::COSIGN_LOGOUT, array('verify' => 'Odhlásiť', 'url'=> self::MAIN_PAGE));
		return parent::logout($connection);
	}

}
