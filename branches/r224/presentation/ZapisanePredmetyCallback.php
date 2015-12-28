<?php
class ZapisanePredmetyCallback implements Renderable {
	private $skusky;
	
	public function __construct($skusky) {
		$this->skusky = $skusky;
	}
	
	public function getHtml() {
		$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
		$predmetyZapisnehoListuTable = new
			Table(TableDefinitions::predmetyZapisnehoListu());
		$predmetyZapisnehoListuCollapsible = new Collapsible('Predmety zápisného listu',
			$predmetyZapisnehoListuTable);
		$kreditovCelkomLeto = 0;
    $kreditovCelkomZima = 0;
    $pocetPredmetovLeto = 0;
    $pocetPredmetovZima = 0;
		foreach (Sorter::sort($predmetyZapisnehoListu->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') {
        $pocetPredmetovLeto += 1;
        $kreditovCelkomLeto += $row['kredit'];
        $class='leto';
      }
      else {
        $pocetPredmetovZima += 1;
        $kreditovCelkomZima += $row['kredit'];
        $class='zima';
      }
			$predmetyZapisnehoListuTable->addRow($row, array('class'=>$class));
			
		}

    $pocetPredmetovText = 'Celkom ';
    $pocetPredmetovText .= FajrUtils::formatPlural($pocetPredmetovLeto+$pocetPredmetovZima,
        '0 predmetov', '1 predmet', '%d predmety', '%d predmetov');
    if ($pocetPredmetovLeto > 0 && $pocetPredmetovZima > 0) {
      $pocetPredmetovText .= sprintf(' (%d v zime, %d v lete)', $pocetPredmetovZima, $pocetPredmetovLeto);
    }

    $kreditovCelkomText = ''. ($kreditovCelkomLeto+$kreditovCelkomZima);
    if ($kreditovCelkomLeto > 0 && $kreditovCelkomZima > 0) {
      $kreditovCelkomText .= sprintf(' (%d+%d)', $kreditovCelkomZima, $kreditovCelkomLeto);
    }

		$predmetyZapisnehoListuTable->addFooter(array('nazov'=>$pocetPredmetovText,'kredit'=>$kreditovCelkomText), array());
		$predmetyZapisnehoListuTable->setUrlParams(array('studium' =>
					Input::get('studium'), 'list' => Input::get('list')));
		
		return $predmetyZapisnehoListuTable->getHtml();
	}
}
