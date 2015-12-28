<?php
/* {{{
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
 * Trieda združujúca rôzne základné veci pre prácu s AISom
 *
 * @author majak
 */
class AIS2Utils
{
	const DATA_PATTERN = '@\<tbody id\=\'dataTabBody0\'\>(.*?)\</tbody\>@s';

	/**
	 * Docasna metoda, nez sa spojenia zrefaktoruju uplne
	 * @staticvar AIS2Connection $connection
	 * @param AIS2Connection $set_to
	 * @return AIS2Connection
	 * @deprecated po zrefaktorovani zmizne
	 */
	public static function connection(AIS2Connection $set_to=null) {
		static $connection = null;

		if ($set_to !== null) {
			$connection = $set_to;
		}

		if ($connection === null) {
			throw new Exception("Nie je nastaveny ziaden connection");
		}

		return $connection;
	}

	/**
	 * Stiahne zadanú stránku a skontroluje ci pri tom nenastala chyba v AISe.
	 * @param string Požadovaná url.
	 * @param array Pole s POST dátami.
	 * @return string Načítaná stránka.
	 * @deprecated Bude sa priamo používať inštancia AIS2Connection
	 */
	public static function request($url, $post = null)
	{
		$connection = self::connection();

		if (is_array($post)) {
			$response = $connection->post($url, $post);
		}
		else {
			$response = $connection->get($url);
		}

		return $response;
	}
	
	/**
	 * predpokladame AIS format datumu a casu, t.j.
	 * vo formate "11.01.2010 08:30"
	 */
	public static function parseAISDateTime($str) {
		// Pozn. strptime() nefunguje na windowse, preto pouzijeme regex
		$pattern =
			'@(?P<tm_mday>[0-3][0-9])\.(?P<tm_mon>[0-1][0-9])\.(?P<tm_year>20[0-9][0-9])'.
			' (?P<tm_hour>[0-2][0-9]):(?P<tm_min>[0-5][0-9]*)@';
		$datum = matchAll($str, $pattern);
		if (!$datum) {
			throw new Exception("Chyba pri parsovaní dátumu a času");
		}
		$datum=$datum[0];
		
		return mktime($datum["tm_hour"],$datum["tm_min"],0,
				$datum["tm_mon"],$datum["tm_mday"],$datum["tm_year"]);
	}
	
	/**
	 * @param str predpokladame range v 2 moznych standardnych ais formatoch
	 *		- "do [datum a cas]"
	 *		- "[datum a cas] do [datum a cas]"
	 * @see parseAISDateTime
	 * @returns array('od'=>timestamp, 'do'=>timestamp)
	 */
	public static function parseAISDateTimeRange($str) {
		$pattern = '@(?P<od>[0-9:. ]*)do (?P<do>[0-9:. ]*)@';
		$data = matchAll($str, $pattern);
		$data = $data[0];
		$result = array();
		if ($data['od'] == '') {
			$result['od'] = null;
		} else {
			$result['od'] = self::parseAISDateTime($data['od']);
		}
		$result['do'] = self::parseAISDateTime($data['do']);
		return $result;
	}
	
}


?>
