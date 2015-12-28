<?php

use fajr\libfajr\base\Trace;
use fajr\libfajr\window\VSES017_administracia_studia as VSES017;

class HodnoteniaCallback implements Renderable {
  private $app;
  
  public function __construct(Trace $trace, VSES017\HodnoteniaPriemeryScreen $app) {
    $this->app = $app;
    $this->trace = $trace;
  }
  
  public function getHtml() {
    $trace = $this->trace->addChild("HodnoteniaCallback");
    $hodnotenia = $this->app->getHodnotenia($trace);
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


    $hodnoteniaCollapsible = new Collapsible(new HtmlHeader('Hodnotenia'), $hodnoteniaTable);
    
    $priemery = $this->app->getPriemery($trace);
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
    

    $priemeryCollapsible = new Collapsible(new HtmlHeader('Priemery'), $priemeryContainer);
    
    return $hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml();
  }
}
