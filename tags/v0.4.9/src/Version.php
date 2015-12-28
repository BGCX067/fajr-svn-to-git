<?php
/**
 * Contains information about version of fajr.
 *
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

/**
 * Various information about fajr version.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Version
{
  /** @var string current version number */
  private static $version = '0.4.9';

  /** @var array basic changelog information */
  private static $changelog = array (
      array('2010-01-xx', '0.1', 'Maják zverejnil prvú verziu Fajr-u'),
      array('2010-02-15', '0.1', 'Fajr sa presunul na google code'),
      array('2010-02-16', '0.1', 'AIS2 sa upgradol, prestali fungovať  niektoré veci'),
      array('2010-05-22', '0.2', 'Pribudli nové tabuľky, skryli sme
             zbytočné stĺpce a celkovo vylepšili vzhľad'),
      array('2010-05-23', '0.2', 'Fajr prešiel na beta testing :-)'),
      array('2010-05-29', '0.2', 'Implementované prihlasovanie a odhlasovanie zo skúšok'),
      array('2010-06-01', '0.25', 'Pribudol zoznam prihlásených na termín'),
      array('2010-10-13', '0.3.0', 'Pridané prihlasovanie cez cosign proxy a vylepšené vnútro fajru'),
      array('2010-11-06', '0.4.0', 'Používa sa template systém. Nové logo.'),
      array('2010-12-01', '0.4.1', 'Konfiguračná voľba pre nastavenie viacerých serverov'),
      array('2010-12-13', '0.4.2', 'Prihlasovanie na skúšky znova funguje'),
      array('2010-12-20', '0.4.3', 'Opravené viaceré chyby'),
      array('2011-01-06', '0.4.4', 'Ďaľšie drobné opravy, nové testy, demo režim'),
      array('2011-01-16', '0.4.5', 'Ďaľšie fixy, testy'),
      array('2011-01-21', '0.4.6', 'Opravy (povolenie prihlasovania na skúšky pri Fx a triedenie mien v zozname prihlásených na skúšku). Základ pre podporu viac vzhľadov'),
      array('2011-02-02', '0.4.7', 'Podpora pre AIS verzie 2.3.21.23'),
      array('2011-02-05', '0.4.8', 'Podpora pre AIS verzie 2.3.21.26'),
      array('2011-04-05', '0.4.9', 'Podpora pre AIS verzie 2.3.22.52'),
    );

  /** @var int how many entries from changelog we want to show */
  private static $changelogLimit = 6;

  /**
   * Return last $changelogLimit changelog entries.
   *
   * @returns array('date'=>string,'version'=>string,'description'=>string)
   *          changelog entries
   */
  public function getChangelog()
  {

    $output = array();
    $tmp_array = array_slice(array_reverse(Version::$changelog), 0, Version::$changelogLimit);
    foreach ($tmp_array as $change) {
      $item = array('date'=>$change[0], 'version'=>$change[1], 'description'=>$change[2]);
      $output[] = $item;
    }
    return $output;
  }

  /**
   * Returns info about svn checkout and versions
   * TODO(ppershing): make this to return object
   *
   * @returns array|null
   */
  public function getBuildTimeInfo()
  {
    @$result = (include '../version_info.php');
    return $result;
  }

  /**
   * Returns string containing current fajr version.
   *
   * @returns string current version number
   */
  public function getVersionNumber()
  {
    return self::$version;
  }
}

?>
