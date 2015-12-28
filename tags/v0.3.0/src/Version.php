<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

class Version
{
  private static $version = '0.3.0';

  private static $changelog = array (
      array('2010-01-xx', '0.1', 'Maják zverejnil prvú verziu Fajr-u'),
      array('2010-02-15', '0.1', 'Fajr sa presunul na google code'),
      array('2010-02-16', '0.1', 'AIS2 sa upgradol, prestali fungovať  niektoré veci'),
      array('2010-05-22', '0.2', 'Pribudli nové tabuľky, skryli sme
             zbytočné stĺpce a celkovo vylepšili vzhľad'),
      array('2010-05-23', '0.2', 'Fajr prešiel na beta testing :-)'),
      array('2010-05-29', '0.2', 'Implementované prihlasovanie a odhlasovanie zo skúšok'),
      array('2010-06-01', '0.25', 'Pribudol zoznam prihlásených na termín'),
      array('2010-10-13', '0.3.0', 'Pridané prihlasovanie cez cosign proxy a vylepšené vnútro fajru')
    );

  private static $changelogLimit = 6;

  public static function getChangelog()
  {
    $data = "<div class='changelog prepend-1 span-21 last increase-line-height'>\n
             <strong>Changelog:</strong><ul>\n";
    $tmp_array = array_slice(array_reverse(Version::$changelog), 0, Version::$changelogLimit);
    foreach ($tmp_array as $change) {
      $data .= '<li>'.$change[0].' (verzia ' . $change[1] . ') - ';
      $data .= $change[2]."</li>\n";
    }
    $data .= "</ul></div>\n";
    return $data;
  }

  public static function getBuildTimeInfo()
  {
    @$result = (include '../version_info.php');
    return $result;
  }

  public static function getVersion()
  {
    return self::$version;
  }

  public static function getVersionString()
  {
    $versionString = self::$version;
    $buildInfo = self::getBuildTimeInfo();
    if ($buildInfo !== false) {
      if (!empty($buildInfo['revision'])) {
        $versionString .= '/'.$buildInfo['revision'];
      }
      if (!empty($buildInfo['date'])) {
        $versionString .= ' ('.date('d.m.Y', $buildInfo['timestamp']).')';
      }
    }
    return $versionString;
  }
}

?>
