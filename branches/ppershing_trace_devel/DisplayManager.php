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

require_once 'FajrConfig.php';

class DisplayManager
{
  protected static $content = array();
  
  protected static $base = null;

  private static $nextHtmlId = 1;
  
  protected static $predefinedContent = array(
      'loginBox' => '
<div class="span-5 prepend-1">
  <form method="post" action="">
    <fieldset class="login-box">
    <legend>Prihlásenie cez Cosign</legend>
    <div>
    <label for="login">Prihlasovacie meno</label>
    <br/>
    <input type="text" name="login" id="login"/>
    <br/>
    <label for="krbpwd">Heslo</label>
    <br/>
    <input type="password" name="krbpwd" id="krbpwd"/>
    <br/>
    <button type="submit" name="submit">
      <img alt="" src="images/key_go.png"/>
      Prihlásiť
    </button>
    </div>
    </fieldset>
  </form>
</div>
<div class="span-5 last">
  <form method="post" action="">
    <fieldset class="login-box">
      <legend>Prihlásenie cez cookie</legend>
    <div>
    bezpečne sa <a
    href=\'https://login.uniba.sk/?cosign-filter-ais2.uniba.sk&amp;https://ais2.uniba.sk/ais/login.do?\'>
      prihlás</a> do AISu a skopíruj si cookie.
    <hr/>
    <label for="cosignCookie">cosign-filter-ais2.uniba.sk</label>
    <br/>
    <input type="password" name="cosignCookie" id="cosignCookie"/>
    <br/>
    <button type="submit" name="submit">
      <img alt="" src="images/key_add.png"/>
      Prihlásiť
    </button>
    </div>
    </fieldset>
  </form>
</div>
<hr class="space" />',

      'header' => '
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="sk" lang="sk">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  ',
      'header2' => '
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
  <script type="text/javascript" src="javascripts/fajr.js"></script>
  <script type="text/javascript" src="javascripts/toggleVisibility.js"></script>
  <script type="text/javascript" src="javascripts/tablesort.min.js"></script>
  <link rel="stylesheet" href="css/screen.css" type="text/css" media="screen, projection" />
  <link rel="stylesheet" href="css/plugins/buttons/screen.css" type="text/css" media="screen, projection" />
  <link rel="stylesheet" href="css/custom.css" type="text/css" media="screen, projection" />
  <link rel="stylesheet" href="css/print.css" type="text/css" media="print" />
  <link rel="stylesheet" href="css/customprint.css" type="text/css" media="print" />
  <!--[if lt IE 8]><link rel="stylesheet" href="css/ie.css" type="text/css" media="screen, projection" /><![endif]-->
  <link href="images/favicon.ico" rel="icon" type="image/x-icon" />
  
  <title>FAJR</title>
  
</head>
<body>
<div class="container"><h1><img src=\'images/fajr_small.gif\' alt="[logo]" class=\'logo\' /> FAJR beta</h1>
',

      'footer' => '
</div>
',
      'footer2'=>'
</body>
</html>
',
      'warnings' => '
<div class="span-18 prepend-1 last increase-line-height">
<p>
Vitajte pred bránou do aplikácie FAJR.
Snahou tejto miniaplikácie je poskytovať najčastejšie používané funkcie
<a href="https://ais2.uniba.sk/">AISu</a> a to jednoducho, rýchlo,
bez zbytočných klikaní a na všetkých browseroch.
Sú dve možnosti ako sa môžeš prihlásiť.
</p>
<ol>
<li>
Pomocou cookie.<br/>
Tento postup je náročnejší, ale bezpečnejší a preto <strong>odporúčaný</strong>.
Funguje to tak, že sa normálne prihlásite do <a href="https://ais2.uniba.sk/">AISu</a>.
Po prihlásení si pozriete nastavené cookies.
Hodnotu tej s názvom "cosign-filter-ais2.uniba.sk" skopírujete do pravého formulára "<em>Prihlásenie cez cookie</em>".
</li>
<li>
Pomocou mena a hesla.<br/>
Do ľavého formulára "<em>Prihlásenie cez Cosign</em>" vyplníte svoje meno a heslo, rovnaké ako keď sa prihlasujete do <a href="https://ais2.uniba.sk/">AISu</a>.
Tento postup <strong>nie je odporúčaný</strong>.
</li>
</ol>
<p>
Prečo je prvý postup bezpečnejší?<br/>
Pretože pri ňom neposielate svoje meno a heslo a nevystavujete sa riziku, že ho niekto po ceste ukradne.
S odcudzenou cookie sa dajú meniť len údaje v <a href="https://ais2.uniba.sk/">AISe</a>
(aj to maximálne najbližších 12 hodín),
s menom a heslom sa dá dostať všade, na čo a kde sa používa.<br/>
</p>
<p>
Táto aplikácia nerobí nič zlé (aspoň jej autori o tom nevedia).
Ale ak jej aj tak nedôverujete, môžete si ju rozbehnúť u seba.
Stačí vám k tomu webserver, PHP a jej
<a href="http://code.google.com/p/fajr/source/checkout">zdrojové kódy</a>
(pre viac informácií viď <a href="http://code.google.com/p/fajr/source/browse/trunk/README">README</a>).
Ak jej ani tak nedôverujete, používajte AIS ;-).
</p>
</div>
',
    'credits' => '
<div class="span-21 prepend-1 last increase-line-height"><p>
Stránka mohla vzniknúť vďaka
<a href="http://www.prototypejs.org/">Prototype</a>,
<a href="http://www.blueprintcss.org/">Blueprint</a>,
<a href="http://www.frequency-decoder.com/2006/09/16/unobtrusive-table-sort-script-revisited">Unobtrusive Table Sort Script</a>
a <a href="http://www.famfamfam.com/lab/icons/silk/">Silk icons</a>.
</p></div>
',
    'terms' => '
<div class="span-21 prepend-1 last increase-line-height"><p>
Prihlásením do systému Fajr súhlasíte s 
<a href="terms_of_use.php">Podmienkami používania</a>
</p></div>
',
    'notConfigured' => '
<div class="prepend-1 span-18 increase-line-height last"><p>Fajr nie je nakonfigurovaný, prosím skopírujte súbor
<code>configuration.example.php</code> do <code>configuration.php</code>.
Prednastavené hodnoty konfiguračných volieb by mali byť vhodné pre väčšinu inštalácií,
no napriek tomu ponúkame možnosť ich pohodlne zmeniť na jednom mieste - v tomto súbore.</p>

<p>
<strong>Dôležité:</strong> Pred používaním aplikácie je ešte nutné správne nastaviť skupinu na
<code>www-data</code> (alebo pod čím beží webserver) a práva na adresáre
<code>./temp</code> a <code>./temp/cookies</code>, tak, aby boli nastavené práva
len na zapisovanie a použitie, t.j. <code>d----wx---</code>.
</p>
</div>
'
  );

  public static function setBase($base)
  {
    self::$base = $base;
  }

  public static function addContent($content, $predefinedContent = false)
  {
    if ($predefinedContent) self::$content[] = self::$predefinedContent[$content];
    else self::$content[] = $content;
  }

  public static function addException($ex)
  {
    $stackTrace = '';
    if (FajrConfig::get('Debug.Exception.ShowStacktrace')) {
      $stackTrace = "\n<b>Stacktrace:</b>\n" . hescape($ex->getTraceAsString());
      $stackTrace = nl2br($stackTrace);
    }
    $info = '<h2>Pri spracúvaní požiadavky nastala chyba:</h2>';
    $info .= nl2br(hescape($ex->getMessage()));
    self::addContent('<div class="error">' . $info . $stackTrace . '</div>');
  }

  public static function display()
  {
    $html = '';
    foreach (self::$content as $item) $html .= $item;
    $header = self::$predefinedContent['header'];
    if (self::$base !== null) $header .= '<base href="'.self::$base.'" />';
    $header .= self::$predefinedContent['header2'];
    $html = $header . $html;
    $html .= self::$predefinedContent['footer'] . self::googleAnalytics() . self::$predefinedContent['footer2'];
    return $html;
  }

  protected static function googleAnalytics() {
    $account = FajrConfig::get('GoogleAnalytics.Account');
    if ($account === null) return '';
    return '<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \''.$account.'\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>';
  }

  public static function getUniqueHTMLId($idType = 'id') {
    $uniquePart = self::$nextHtmlId;
    self::$nextHtmlId += 1;

    return $idType.$uniquePart;
  }
}

?>
