<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??):missing author

/**
 *
 * @package    Fajr
 * @filesource
 */
namespace fajr\presentation;
use fajr\htmlgen\Renderable;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017;
use fajr\htmlgen\Table;
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\HtmlHeader;
use fajr\htmlgen\Container;
use fajr\htmlgen\Label;
use fajr\TableDefinitions;
use fajr\PriemeryCalculator;
use fajr\Sorter;

class HodnoteniaCallback implements Renderable
{
  private $app;
  
  public function __construct(Trace $trace, VSES017\HodnoteniaPriemeryScreen $app)
  {
    $this->app = $app;
    $this->trace = $trace;
  }
  
  public function getHtml()
  {
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
      } else {
        $class='zima';
        $priemeryCalculator->add(PriemeryCalculator::SEMESTER_ZIMNY,
                                 $row['znamka'], $row['kredit']);
      }
      $hodnoteniaTable->addRow($row, array('class'=>$class));
    }

    $hodnoteniaCollapsible = new Collapsible(new HtmlHeader('Hodnotenia'),
                                             $hodnoteniaTable);
    
    $priemery = $this->app->getPriemery($trace);
    $priemeryTable = new Table(TableDefinitions::priemery());
    $priemeryTable->addRows($priemery->getData());

    $priemeryContainer = new Container();
    $priemeryContainer->addChild(new Label('Nasledovné priemery sú prebraté
        z AISu, čiže to (ne)funguje presne rovnako:'));
    $priemeryContainer->addChild($priemeryTable);

    if ($priemeryCalculator->hasPriemer()) {
      $priemeryFajrText = '<p><br />Nasledovné vážené študijné priemery sú
          počítané Fajrom priebežne z tabuľky Hodnotenia, <strong>preto
          nemôžu byť považované za oficiálne</strong>:<br /><br />';
      $priemeryFajrText .= $priemeryCalculator->getHtml();
      $priemeryFajrText .= '</p>';

      $priemeryContainer->addChild(new Label($priemeryFajrText));
    }

    $priemeryCollapsible = new Collapsible(new HtmlHeader('Priemery'),
                                           $priemeryContainer);
    
    return $hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml();
  }
}
