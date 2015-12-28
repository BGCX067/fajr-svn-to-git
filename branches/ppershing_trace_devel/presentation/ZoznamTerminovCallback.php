<?php
use fajr\libfajr\base\Trace;

class ZoznamTerminovCallback implements Renderable {
  private $skusky;
  private $hodnotenia;
  private $trace;
  private $connection;
  
  public function __construct(Trace $trace, $skusky, $hodnotenia) {
    $this->trace = $trace;
    $this->skusky = $skusky;
    $this->hodnotenia = $hodnotenia;
  }
  
  public function hashNaPrihlasenie($predmet, $row) {
    return
      md5($row['index'].'|'.$row['dat'].'|'.$row['cas'].'|'.$predmet);
    
  }
  
  public function prihlasNaSkusku($predmetIndex, $terminIndex)
  {
    $predmety = $this->skusky->getPredmetyZapisnehoListu()->getData();
    $predmetKey = -1;
    foreach ($predmety as $key=>$row) {
      if ($row['index']==$predmetIndex) $predmetKey = $key;
    }
    
    $terminy =
      $this->skusky->getZoznamTerminovDialog($predmetIndex)->getZoznamTerminov()->getData();
    $terminKey = -1;
    foreach($terminy as $key=>$row) {
      if ($row['index']==$terminIndex) $terminKey = $key;
    }
    if ($predmetKey == -1 || $terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }
    
    $hash = $this->hashNaPrihlasenie($predmety[$predmetIndex]['nazov'],
        $terminy[$terminIndex]);
    if ($hash != Input::get('hash')) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }
    return $this->skusky->getZoznamTerminovDialog($predmetIndex)->prihlasNaTermin($terminIndex);
  }
  
  const PRIHLASIT_MOZE = 0;
  const PRIHLASIT_MOZE_ZNAMKA = -1;
  const PRIHLASIT_NEMOZE_CAS = 1;
  const PRIHLASIT_NEMOZE_POCET = 2;
  const PRIHLASIT_NEMOZE_ZNAMKA = 3;
  const PRIHLASIT_NEMOZE_INE = 4;
  
  public function mozeSaPrihlasit($row) {
    $prihlasRange = AIS2Utils::parseAISDateTimeRange($row['prihlasovanie']);
    $predmet = $row['predmet'];
    if (isset($this->hodnoteniaData[$predmet]['znamka'])) {
      $znamka = $this->hodnoteniaData[$predmet]['znamka'];
    } else {
      $znamka = "";
    }

    if (isset($this->hodnoteniaData[$predmet]['mozePrihlasit']) &&
        $this->hodnoteniaData[$predmet]['mozePrihlasit']=='N') {
      $mozePredmet = false;
    } else {
      $mozePredmet = true;
    }

    if ($znamka!="" && $znamka!="FX" && !$mozePredmet) {
      return self::PRIHLASIT_NEMOZE_ZNAMKA;
    }

    if (!($prihlasRange['od'] < time() && $prihlasRange['do']>time())) {
      return self::PRIHLASIT_NEMOZE_CAS;
    }
    if ($row['maxPocet'] != '' &&
        $row['maxPocet']==$row['pocetPrihlasenych']) {
      return self::PRIHLASIT_NEMOZE_POCET;
    }

    if (!$mozePredmet) {
      return self::PRIHLASIT_NEMOZE_INE;
    }

    if ($znamka!="" && $znamka!="FX" && $mozePredmet) {
      return self::PRIHLASIT_MOZE_ZNAMKA;
    }

    return self::PRIHLASIT_MOZE;
  }
  
  public function getHtml() {
    $predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu($this->trace);
    $hodnoteniaData = array();
    
    foreach ($this->hodnotenia->getHodnotenia($this->trace)->getData() as $row) {
      $hodnoteniaData[$row['nazov']]=$row;;
    }
    $this->hodnoteniaData = $hodnoteniaData;
    
    if (Input::get('action') !== null) {
      assert(Input::get("action")=="prihlasNaSkusku");
      if ($this->prihlasNaSkusku(Input::get("prihlasPredmetIndex"), Input::get("prihlasTerminIndex")))
      {
        FajrUtils::redirect(array('tab' => 'TerminyHodnotenia'));
      }
      else throw new Exception('Na skúšku sa nepodarilo prihlásiť.');
    }
    
    $baseUrlParams = array("studium"=>Input::get("studium"),
          "list"=>Input::get("list"),
          "tab"=>Input::get("tab"));
    
    $terminyTable = new
      Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), array('termin'=>'index',
            'predmet'=>'predmetIndex'), $baseUrlParams);

    $terminyCollapsible = new Collapsible(new HtmlHeader('Termíny, na ktoré sa môžem prihlásiť'),
      $terminyTable);
    
    $actionUrl=FajrUtils::linkUrl($baseUrlParams);
    
    foreach ($predmetyZapisnehoListu->getData() as $predmetRow) {
      
      $dialog = $this->skusky->getZoznamTerminovDialog(
          $this->trace->addChild('Get zoznam terminov'), $predmetRow['index']);
      $terminy = $dialog->getZoznamTerminov($this->trace->addChild('Get zoznam terminov'));
      foreach($terminy->getData() as $row) {
        $row['predmet']=$predmetRow['nazov'];
        $row['predmetIndex']=$predmetRow['index'];
        
        $hash = $this->hashNaPrihlasenie($predmetRow['nazov'], $row);
        $mozeSaPrihlasit = $this->mozeSaPrihlasit($row);
        if ($mozeSaPrihlasit == self::PRIHLASIT_MOZE ||
            $mozeSaPrihlasit == self::PRIHLASIT_MOZE_ZNAMKA) {
          $row['prihlas']="<form method='post' action='$actionUrl'><div>
              <input type='hidden' name='action' value='prihlasNaSkusku'/>
              <input type='hidden' name='prihlasPredmetIndex'
              value='".$row['predmetIndex']."'/>
              <input type='hidden' name='prihlasTerminIndex'
              value='".$row['index']."'/>
              <input type='hidden' name='hash' value='$hash'/>
              <button name='submit' type='submit' class='tableButton positive'>
                <img src='images/add.png' alt=''>Prihlás ma!
              </button></div></form>";
          if ($mozeSaPrihlasit == self::PRIHLASIT_MOZE_ZNAMKA) {
            $row['prihlas'] = 'Už máš zápísané"'.
              $hodnoteniaData[$row['predmet']]['znamka'].'"'.$row['prihlas'];
          }
        } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_CAS) {
          $row['prihlas'] = 'Nedá sa (neskoro)';
        } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_POCET) {
          $row['prihlas'] = 'Termín je plný!';
        } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_ZNAMKA) {
          $row['prihlas'] = 'Už máš zápísané"'.
            $hodnoteniaData[$row['predmet']]['znamka'].'"';
        } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_INE) {
          $row['prihlas'] = 'Nedá sa, dôvod neznámy';
        }
        $terminyTable->addRow($row, null);
        
      }
    }
    if (Input::get('termin')!=null && Input::get('predmet')!=null) {
      $terminyTable->setOption('selected_key',
          array('index'=>Input::get('termin'),
            'predmetIndex'=>Input::get('predmet')));
    }
    
    $html = $terminyCollapsible->getHtml();
    if (Input::get('termin') != null && Input::get('predmet')!=null) {
      $prihlaseni = $this->skusky->getZoznamTerminovDialog(Input::get('predmet'))
        ->getZoznamPrihlasenychDialog(Input::get('termin'))
        ->getZoznamPrihlasenych();
      
      $zoznamPrihlasenychTable =  new
      Table(TableDefinitions::zoznamPrihlasenych(), null, array('studium', 'list'));

      $zoznamPrihlasenychCollapsible = new Collapsible('Zoznam prihlásených
          na vybratý termín', $zoznamPrihlasenychTable);
      
      $zoznamPrihlasenychTable->addRows($prihlaseni->getData());
      $html .= $zoznamPrihlasenychCollapsible->getHtml();
    }
    return $html;
  }
}
