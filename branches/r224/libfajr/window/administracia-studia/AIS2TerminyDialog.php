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
 * Trieda pre dialóg s termínmi skúšok k jednému predmetu.
 *
 * @author majak
 */
/*abstract */class AIS2TerminyDialog extends AIS2AbstractDialog
{

	protected $tabulka_vyber_terminu_hodnotenia = array(
		// {{{
		'kodFaza',
		'dat',
		'cas',
		'miestnosti',
		'pocetPrihlasenych',
		'maxPocet',
		'pocetHodn',
		'hodnotiaci',
		'prihlasovanie',
		'odhlasovanie',
		'poznamka',
		'zaevidoval',
		// }}}
	);
	
	public function getZoznamTerminov()
	{
		$this->open();
		$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
		return new AIS2Table($this->tabulka_vyber_terminu_hodnotenia, $data[0][1]);
	}
	
	public function prihlasNaTermin($terminIndex)
	{
		$this->open();
		$data = $this->requestData(array(
			'compName' => 'enterAction',
			'eventClass' => 'avc.ui.event.AVCActionEvent',
			'embObj' => array(
				'objName' => 'zoznamTerminovTable',
				'dataView' => array(
					'activeIndex' => $terminIndex,
					'selectedIndexes' => $terminIndex,
				),
			),
		));
		
		$error = match($data, '@webui\.messageBox\("([^"]*)",@');
		if ($error) throw new Exception('Nepodarilo sa prihlásiť na zvolený termín.<br/>Dôvod: <b>'.$error.'</b>');
		
		$this->terminated = true; // po uspesnom prihlaseni za dialog hned zavrie
		return true;
	}
	
	public function getZoznamPrihlasenychDialog($terminIndex)
	{
		return new AIS2ZoznamPrihlasenychDialog($this, 'zobrazitZoznamPrihlasenychAction', 'zoznamTerminovTable', $terminIndex);
	}
	
}
?>
