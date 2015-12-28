<?php

class HodnoteniaCallback implements Renderable {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	
	public function getHtml() {
		$hodnotenia = $this->app->getHodnotenia();
		$hodnoteniaTable = new Table(TableDefinitions::hodnotenia());
		$priemeryCalculator = new PriemeryCalculator();

		foreach(Sorter::sort($hodnotenia->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') {
				$class='leto';
				$priemeryCalculator->add(PriemeryCalculator::SEMESTER_LETNY,
					$row['znamka'], $row['kredit']);
			}
			else {
				$class='zima';
				$priemeryCalculator->add(PriemeryCalculator::SEMESTER_ZIMNY,
					$row['znamka'], $row['kredit']);
			}
			$hodnoteniaTable->addRow($row, array('class'=>$class));
		}


		$hodnoteniaCollapsible = new Collapsible('Hodnotenia', $hodnoteniaTable);
		
		$priemery = $this->app->getPriemery();
		$priemeryTable = new Table(TableDefinitions::priemery());
		$priemeryTable->addRows($priemery->getData());

		$priemeryContainer = new Container();
		$priemeryContainer->addChild(new Label('Nasledovné priemery sú prebraté z AISu, čiže to (ne)funguje presne rovnako:'));
		$priemeryContainer->addChild($priemeryTable);

		if ($priemeryCalculator->hasPriemer()) {
			$priemeryFajrText = '<p><br />Nasledovné vážené študijné priemery sú počítané Fajrom priebežne z tabuľky Hodnotenia, <strong>preto nemôžu byť považované ako oficiálne</strong>:<br /><br />';
			$priemeryFajrText .= $priemeryCalculator->getHtml();
			$priemeryFajrText .= '</p>';

			$priemeryContainer->addChild(new Label($priemeryFajrText));
		}
		

		$priemeryCollapsible = new Collapsible('Priemery', $priemeryContainer);
		
		return $hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml();
	}
}
