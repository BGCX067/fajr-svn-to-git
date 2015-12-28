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

use fajr\config\ServerConfig;
use fajr\config\SkinConfig;

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
   * Adresár s certifikátmi pre SSL spojenie (null je curl default).
   * Pri zmene certifikátov netreba zabudnúť spustiť "c_rehash".
   */
  //'SSL.CertificatesDir'=>null,

  /*
   * Pri zapnutí tejto voľby bude fajr vyžadovať SSL spojenie.
   * Pri vypnutí voľby dôjde k uvoľneniu politiky cookies (vypne sa príznak
   * secure), aby sessions fungovali aj cez nezabezpečené spojenie.
   *
   * Vypnutie SSL.Require sa na produkčnej inštalácii neodporúča!
   */
  //'SSL.Require'=>true,

  /*
   * User agent pod akým sa má libfajr identifikovať.
   */
  //'Connection.UserAgent'=>'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7',

  /*
   * Použitý template
   */
  //'Template.Directory'=>'./templates',

  /*
   * Má sa použiť cache na skompilované templaty?
   * Upozornenie: Cache na templaty je po každom update
   * fajru treba vymazať!
   */
  //'Template.Cache'=>false,

  /*
   * Cache na skompilované templaty
   * (absolútna, alebo relatívna k adresáru Path.Temporary)
   */
  //'Template.Cache.Path'=>'./twig_cache',

  /*
   * Defaultný skin pre Fajr.
   */
  //'Template.Skin.Default'=>'fajr',

  /*
   * Konfigurácia skinov
   *
    'Template.Skin.Skins' =>
      array('noskin' => new SkinConfig(
              array(
                'name' => 'noskin',
                'internal' => true,
                'path' => '',
              )),
            'fajr' => new SkinConfig(
              array(
                'name' => 'default',
                'path' => 'fajr',
                'parent' => 'noskin',
                ))
            ),
    */

  /*
   * Názov defaultného servera (viď zoznam serverov)
   */
  'AIS2.DefaultServer' => 'ais2.uniba.sk',

  /*
   * Zoznam serverov a ich konfigurácia.
   * Konfigurácia servera pozostáva z nasledujúcich položiek:
   * - Hostname AIS2 servera (musí sedieť s kľúčom asociatívneho poľa)
   *   'AIS2.ServerName' => 'ais2.uniba.sk',
   * - Názov inštancie AIS2 (Text, ktorý sa zobrazuje používateľom)
   *   'AIS2.InstanceName' => 'AIS2',
   * - Typ prihlásenia (password, cosign, cosignproxy)
   *   'Login.Type'=>'password',
   * - Adresár pre proxy súbory cosignu
   *   'Login.Cosign.ProxyDB'=>'',
   * - Názov AIS-ovej cosign cookie
   *   'Login.Cosign.CookieName'=>'cosign-filter-ais2.uniba.sk',
   * - Je daná inštancia ostrá verzia AISu?
   *   'Server.Beta'=>false
   * - Typ serveru
   *   'Backend'=>ServerConfig::BACKEND_LIBFAJR
   */
  'AIS2.ServerList' => array(
    'ais2.uniba.sk' => new ServerConfig(
      array(
        'Server.InstanceName' => 'AIS2',
        'Server.Name' => 'ais2.uniba.sk',
        'Login.Type' => 'cosign',
        'Login.Cosign.CookieName' => 'cosign-filter-ais2.uniba.sk',
        'Server.Beta' => false,
        )),
    'ais2-beta.uniba.sk' => new ServerConfig(
      array(
        'Server.InstanceName' => 'AIS2-Beta',
        'Server.Name' => 'ais2-beta.uniba.sk',
        'Login.Type' => 'cosign',
        'Login.Cosign.CookieName' => 'cosign-filter-ais2-beta.uniba.sk',
        'Server.Beta' => true,
        )),
    'fajr.demo' => new ServerConfig(
      array(
        'Server.InstanceName' => 'Fajr Demo',
        'Server.Name' => 'fajr.demo',
        'Login.Type' => 'nologin',
        'Server.Beta' => false,
        'Backend' => ServerConfig::BACKEND_FAKE
        )),
    ),

  /*
   * Je toto development verzia? (Obsahuje novú funkcionalitu, ktorá je zatiaľ v testovaní)
   */
  // 'Features.Devel' => false,
);
