<?php

use fajr\libfajr\base\Trace;
use fajr\libfajr\window\VSES017_administracia_studia as VSES017;

class MojeTerminyHodnoteniaCallback implements Renderable {
  public function __construct(Trace $trace,
                              VSES017\TerminyHodnoteniaScreen $terminyHodnotenia,
                              VSES017\HodnoteniaPriemeryScreen $hodnotenia) {
    $this->trace = $trace;
    $this->terminyHodnoteniaApp = $terminyHodnotenia;
    $this->hodnoteniaApp = $hodnotenia;
  }
  
  /**
   * ked odhlasujeme z predmetu, narozdiel od AISu robime opat
   * inicializaciu vsetkych aplikacii. Just for sure chceme
   * okontrolovat, ze sa nic nezmenilo a ze sme dostali rovnake data
   * ako predtym!
   */
  private function hashNaOdhlasenie($row) {
    return
      md5($row['index'].'|'.$row['datum'].'|'.$row['cas'].'|'.$row['predmet']);
  }
  
  private function odhlasZoSkusky($terminIndex) {
    
    $terminy = $this->terminyHodnoteniaApp->getTerminyHodnotenia($this->trace->addChild('get terminy
          hodnotenia '))->getData();
    $terminKey = -1;
    foreach ($terminy as $key=>$row) {
      if ($row['index']==$terminIndex) $terminKey = $key;
    }
    if ($terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }
    if (Input::get("hash") != $this->hashNaOdhlasenie($terminy[$terminKey])) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }
    return $this->terminyHodnoteniaApp->odhlasZTerminu($terminIndex);
  }
  
  
  public function getHtml() {
    $trace = $this->trace->addChild("MojeTerminyHodnoteniaCallback");
    $trace->tlog("Executing callback");
    $terminyHodnotenia = $this->terminyHodnoteniaApp->getTerminyHodnotenia(
        $trace->addChild("get terminy hodnotenia"));
    $hodnotenia = $this->hodnoteniaApp->getHodnotenia(
        $trace->addChild("get hodnotenia"));
    if (Input::get('action') !== null) {
      $trace->tlog("odhlasujem zo skusky");
      assert(Input::get("action")=="odhlasZoSkusky");
      if ($this->odhlasZoSkusky(Input::get("odhlasIndex")))
      {
        FajrUtils::redirect();
      }
      else throw new Exception('Z termínu sa nepodarilo odhlásiť.');
    }
    
    $baseUrlParams = array("studium"=>Input::get("studium"),
          "list"=>Input::get("list"),
          "tab"=>Input::get("tab"));
    
    $terminyHodnoteniaTableActive =  new
      Table(TableDefinitions::mojeTerminyHodnotenia(), 'termin', $baseUrlParams);

    $terminyHodnoteniaCollapsibleActive = new Collapsible(
        new HtmlHeader('Aktuálne termíny hodnotenia'),
        $terminyHodnoteniaTableActive);
    
    $terminyHodnoteniaTableOld =  new
      Table(TableDefinitions::mojeTerminyHodnotenia(), 'termin', $baseUrlParams);

    $terminyHodnoteniaCollapsibleOld = new Collapsible(
        new HtmlHeader('Staré termíny hodnotenia'),
        $terminyHodnoteniaTableOld);
    
    if (Input::get('termin')!=null) {
      $terminyHodnoteniaTableActive->setOption('selected_key',
          Input::get('termin'));
      $terminyHodnoteniaTableOld->setOption('selected_key',
          Input::get('termin'));
    }
    
    $actionUrl=FajrUtils::linkUrl($baseUrlParams);
    
    $hodnoteniePredmetu=array();
    foreach($hodnotenia->getData() as $row) {
      $hodnoteniePredmetu[$row['nazov']]=$row['znamka'];
    }
    
    foreach($terminyHodnotenia->getData() as $row) {
      $datum = AIS2Utils::parseAISDateTime($row['dat']." ".$row['cas']);
      
      if ($row['znamka']=="") { // skusme najst znamku v hodnoteniach
        if (isset($hodnoteniePredmetu[$row['predmet']]) &&
              $hodnoteniePredmetu[$row['predmet']]!="") {
            $row['znamka'] =
                $hodnoteniePredmetu[$row['predmet']]." (z&nbsp;predmetu)";
            }
      }
          
      if ($datum < time()) {
        $row['odhlas']="Skúška už bola.";
        if ($row['jePrihlaseny']=='A') {
          $terminyHodnoteniaTableOld->addRow($row, null);
        }
      } else {
        if ($row['mozeOdhlasit']==1) {
          $class='terminmozeodhlasit';
          $hash=$this->hashNaOdhlasenie($row);
          $row['odhlas']="<form method='post' action='$actionUrl'>
            <div>
            <input type='hidden' name='action' value='odhlasZoSkusky'/>
            <input type='hidden' name='odhlasIndex'
            value='".$row['index']."'/>
            <input type='hidden' name='hash' value='$hash'/>
            <button name='submit' type='submit' class='tableButton negative'>
              <img src='images/cross.png' alt=''>Odhlás
            </button></div></form>";
        } else {
          $row['odhlas']="nedá sa";
          $class='terminnemozeodhlasit';
        }
          
        if ($row['prihlaseny']!='A') {
          $row['odhlas']='Si odhlásený. Ak chceš, opäť sa prihlás.';
          $class='terminodhlaseny';
        }
        $terminyHodnoteniaTableActive->addRow($row, array('class'=>$class));
      }
    }

    $html = $terminyHodnoteniaCollapsibleActive->getHtml();
    $html .= $terminyHodnoteniaCollapsibleOld->getHtml();
    if (Input::get('termin')!=null) {
      $prihlaseni = $this->terminyHodnoteniaApp->getZoznamPrihlasenychDialog($trace,
          Input::get('termin'))->getZoznamPrihlasenych($trace);
      $zoznamPrihlasenychTable = new
      Table(TableDefinitions::zoznamPrihlasenych(), null, array('studium', 'list'));
      $zoznamPrihlasenychTable->addRows($prihlaseni->getData());
      $zoznamPrihlasenychCollapsible = new Collapsible(new HtmlHeader('Zoznam prihlásených
          na vybratý termín'), $zoznamPrihlasenychTable);
      $html .= $zoznamPrihlasenychCollapsible->getHtml();
    }
    return $html;
  }
}
