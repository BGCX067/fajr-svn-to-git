<?php
/*
 * Konfiguračný súbor fajru
 *
 */

return array(
  /*
   * Ak táto voľba obsahuje tracking code na Google Analytics,
   * do stránky sa vloží potrebný skript na trackovanie. V prípade,
   * že sa Google Analytics nemá použiť, ponechajte túto voľbu zakomentovanú.
   */
  //'GoogleAnalytics.Account'=>'UA-680810-11',

  /*
   * Ak je táto voľba zapnutá, fajr bude vypisovať komunikáciu medzi
   * ním a AISom. Neodporúčame používať na produkčných inštaláciách, keďže
   * spôsobuje, že na výstupe stránky sa objaví obrovské množstvo dát.
   * Predvolená hodnota false vypne debugovanie spojení.
   */
  //'Debug.Connections'=>true,

  /*
   * Ak je táto voľba zapnutá, budú sa používať cesty tvaru fajr.php/nieco.
   * Predvolená hodnota false znamená, že sa takéto cesty nebudú používať
   * (najväčšia kompatibilita).
   */
  //'URL.Path'=>true,

  /*
   * Ak je táto voľba zapnutá, URL-ka aplikácie nebudú obsahovať časť
   * "fajr.php". Aplikácia v tomto prípade bude fungovať, len ak je správne
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
);
