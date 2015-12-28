<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Konfiguračný súbor fajru
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
return array(
  /*
   * Ak táto voľba obsahuje tracking code na Google Analytics,
   * do stránky sa vloží potrebný skript na trackovanie. V prípade,
   * že sa Google Analytics nemá použiť, ponechajte túto voľbu zakomentovanú.
   */
  //'GoogleAnalytics.Account'=>'UA-680810-11',

  /*
   * Zapnutím voľby sa začne zobrazovat varujúca hláška o development verzii
   * a redirect na ostrú verziu fajr.dcs.fmph.uniba.sk
   */
  //'Debug.Banner'=>true,

  /*
   * Ak je táto voľba zapnutá, fajr bude vypisovať komunikáciu medzi
   * ním a AISom. Neodporúčame používať na produkčných inštaláciách, keďže
   * spôsobuje, že na výstupe stránky sa objaví obrovské množstvo dát.
   * Predvolená hodnota false vypne debugovanie spojení a chodu fajru.
   */
  //'Debug.Trace'=>true,

  /**
   * Ak je táto voľba zapnutá, pri zobrazovaní výnimiek sa vypíše kompletný stacktrace.
   */
  //'Debug.Exception.ShowStacktrace'=>true,

  /*
   * Ak je táto voľba zapnutá, budú sa používať cesty tvaru index.php/nieco.
   * Predvolená hodnota false znamená, že sa takéto cesty nebudú používať
   * (najväčšia kompatibilita).
   */
  //'URL.Path'=>true,

  /*
   * Ak je táto voľba zapnutá, URL-ka aplikácie nebudú obsahovať časť
   * "index.php". Aplikácia v tomto prípade bude fungovať, len ak je správne
   * nastavený a povolený mod_rewrite, či jeho ekvivalent.
   * Táto voľba má účinok len vtedy, keď je zapnutá voľba URL.Path
   */
  //'URL.Rewrite'=>true,

  /*
   * Cesta k adresáru pre dočasné súbory (absolútna,
   * alebo relatívna k adresáru projektu)
   */
  //'Path.Temporary'=>'./temp',

  /*
   * Cesta k adresáru pre cookies súbory (absolútna,
   * alebo relatívna k adresáru Path.Temporary)
   */
  //'Path.Temporary.Cookies'=>'./cookies',

  /*
   * Cesta k adresáru pre session súbory (absolútna,
   * alebo relatívna k adresáru Path.Temporary)
   */
  //'Path.Temporary.Sessions'=>'./sessions',

  /*
   * Hostname AIS2 servera
   */
  //'AIS2.ServerName' => 'ais2.uniba.sk',

  /*
   * Názov inštancie AIS2
   * (Text, ktorý sa zobrazuje používateľom)
   */
  //'AIS2.InstanceName' => 'AIS2',

  /*
   * Typ prihlásenia (password alebo cosign)
   */
  //'Login.Type'=>'password',

  /*
   * Adresár pre proxy súbory cosignu
   */
  //'Login.Cosign.ProxyDB'=>'',

  /*
   * Názov AIS-ovej cosign cookie
   */
  //'Login.Cosign.CookieName'=>'cosign-filter-ais2.uniba.sk',

  /*
   * Adresár s certifikátmi pre SSL spojenie (null je curl default).
   * Pri zmene certifikátov netreba zabudnúť spustiť "c_rehash".
   */
  //'SSL.CertificatesDir'=>null,

  /*
   * User agent pod akým sa má libfajr identifikovať.
   */
  //'Connection.UserAgent'=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7',

  /*
   * Použitý template
   */
  //'Template.Directory'=>'./templates/fajr',
);
