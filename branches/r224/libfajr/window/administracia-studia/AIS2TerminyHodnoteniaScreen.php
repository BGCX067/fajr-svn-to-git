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
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @author majak
 */
class AIS2TerminyHodnoteniaScreen extends AIS2AbstractScreen
{
	protected $tabulka_predmety_zapisneho_listu = array(
		// {{{
		'kodCastStPlanu',
		'kodTypVyucby',
		'skratka',
		'nazov',
		'kredit',
		'semester',
		'sposobUkoncenia',
		'pocetTerminov',
		'pocetAktualnychTerminov',
		'aktualnost',
		// }}}
	);
	protected $tabulka_terminy_hodnotenia = array(
		// {{{
		'prihlaseny',
		'faza',
		'datum',
		'cas',
		'miestnosti',
		'pocetPrihlasenych',
		'datumPrihlasenia',
		'datumOdhlasenia',
		'zapisal',
		'pocetHodnotiacich',
		'hodnotiaci',
		'maxPocet',
		'znamka',
		'prihlasovanie',
		'odhlasovanie',
		'poznamka',
		'zaevidoval',
		'mozeOdhlasit',
		'skratkaPredmetu',
		'predmet',
		// }}}
	);

	public function __construct($idZapisnyList, $idStudium)
	{
		parent::__construct('ais.gui.vs.es.VSES007App', '&kodAplikacie=VSES007&idZapisnyList='.$idZapisnyList.'&idStudium='.$idStudium);
	}

	public function getPredmetyZapisnehoListu()
	{
		$this->open();
		$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
		return new AIS2Table($this->tabulka_predmety_zapisneho_listu, $data[0][1]);
	}

	public function getTerminyHodnotenia()
	{
		$this->open();
		$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
		return new AIS2Table($this->tabulka_terminy_hodnotenia, $data[1][1]);
	}

	public function getZoznamTerminovDialog($predmetIndex)
	{
		return new AIS2TerminyDialog($this, 'pridatTerminAction', 'predmetyTable', $predmetIndex);
	}
	
	public function getZoznamPrihlasenychDialog($terminIndex)
	{
		return new AIS2ZoznamPrihlasenychDialog($this, 'zoznamPrihlasenychStudentovAction', 'terminyTable', $terminIndex);
	}
	
	public function odhlasZTerminu($terminIndex)
	{
		$this->open();
		// Posleme request ze sa chceme odhlasit.
		$data = $this->requestData(array(
			'compName' => 'odstranitTerminAction',
			'eventClass' => 'avc.ui.event.AVCActionEvent',
			'embObj' => array(
				'objName' => 'terminyTable',
				'dataView' => array(
					'activeIndex' => $terminIndex,
					'selectedIndexes' => $terminIndex,
				),
			),
		));
		
		// Odklikneme konfirmacne okno ze naozaj.
		$data = $this->requestData(array(
			'events' => false,
			'app' => false,
			'dlgName' => false,
			'changedProperties' => array(
				'confirmResult' => 2,
			),
		));
		
		if (!preg_match('@dialogManager\.openDialog\("PleaseWaitDlg0"@', $data)) throw new Exception('Z termínu sa nepodarilo odhlásiť.<br/>Pravdepodobne termín s daným indexom neexistuje.');
		
		// Nacitame loading obrazovku.
		$data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&form=PleaseWaitDlg0&antiCache='.random());
		
		// Zavrieme loading obrazovku. Az po tomto kroku sme naozaj odhlaseni.
		$data = $this->requestData(array(
			'events' => false,
			'dlgName' => false,
			'appProperties' => array(
				'activeDlgName' => 'PleaseWaitDlg0',
			),
		));
		
		$message = match($data, '@webui\.messageBox\("([^"]*)"@');
		if (($message !== false) && ($message != 'Činnosť úspešne dokončená.')) throw new Exception("Z termínu sa (pravdepodobne) nepodarilo odhlásiť. Dôvod:<br/><b>".$message.'</b>');
		
		return true;
	}

}

?>
