<?php
/**
 * Contains controller managing user preferences.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Settings
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\settings;

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\SkinConfig;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\base\Preconditions;
use sfStorage;

class SkinSettings extends DisableEvilCallsObject {
  /** key under which are skin settings in storage */
  const SETTINGS_SKIN_NAME_KEY = '/settings/skin/name';

  /** @var array(SkinConfig) list of all available skins */
  private $skins;

  /** @var string name of the default skin */
  private $defaultSkinName;

  /** @var sfStorage|null */
  private $settingsStorage;

  /**
   * Constructs settings object. Omitting $settingsStorage will
   * result in "unmodifiable and default" settings.
   *
   * @param FajrConfig $config fajr configuration
   * @param sfStorage $settingsStorage user settings storage
   */
  public function __construct(FajrConfig $config, sfStorage $settingsStorage = null) {
    $this->settingsStorage = $settingsStorage;

    $allSkins = $config->get(FajrConfigOptions::TEMPLATE_SKINS);
    foreach ($allSkins as $skin) {
      Preconditions::check($skin instanceof SkinConfig);
    }

    $skins = array();
    foreach ($allSkins as $key=>$skin) {
      if (!$skin->isInternal()) {
        $skins[$key] = $skin;
      }
    }
    $this->skins = $skins;

    $default = $config->get(FajrConfigOptions::TEMPLATE_DEFAULT_SKIN);
    if (!in_array($default, array_keys($this->skins))) {
      throw new RuntimeException("Default skin is not available!");
    }
    $this->defaultSkinName = $default;
  }

  /**
   * @returns array(SkinConfig) all available skins
   */
  public function getAvailableSkins() {
    return $this->skins;
  }

  /**
   * @returns string name of the default skin
   */
  public function getDefaultSkinName()
  {
    return $this->defaultSkinName;
  }

  /**
   * @param string $name name of the skin to retrieve
   *
   * @returns SkinConfig
   * @throws IllegalArgumentException
   */
  public function getSkinByName($name)
  {
    Preconditions::checkIsString($name);
    if (!in_array($name, array_keys($this->skins))) {
      throw new IllegalArgumentException("You must specify a valid skin!");
    }
    return $this->skins[$name];
  }

  /**
   * Return current skin name from user settings.
   *
   * Note: If there is no settings store supplied in constructor,
   * we will return default skin name.
   * Note: If skin saved in settings storage is invalid, we will silently
   * ignore it and return default. This is useful behaviour if you remove
   * skin but there were many users using it before.
   *
   * @returns string
   */
  public function getUserSkinName() {
    if ($this->settingsStorage === null) {
      return $this->getDefaultSkinName(); // silently ignore
    }

    $name = $this->settingsStorage->read(self::SETTINGS_SKIN_NAME_KEY);
    if (!in_array($name, array_keys($this->skins))) {
      return $this->getDefaultSkinName();
    };
    return $name;
  }

  /**
   * Return skin from user settings.
   *
   * Note: If there is no settings store supplied in constructor,
   * we will return default skin.
   * Note: If skin saved in settings storage is invalid, we will silently
   * ignore it and return default.
   *
   * @returns SkinConfig
   */
  public function getUserSkin()
  {
    $name = $this->getUserSkinName();
    assert(isset($this->skins[$name]));
    return $this->skins[$name];
  }

  /**
   * Stores the new skin name into storage.
   *
   * Note: If there is no settings storage supplied in constructor,
   * we will silently ignore any changes made.
   *
   * @param string $newSkinName
   *
   * @returns void
   */
  public function setUserSkinName($newSkinName) {
    if ($this->settingsStorage === null) {
      return; // silently ignore
    }

    if (!in_array($newSkinName, array_keys($this->skins))) {
      throw new Exception("Neexistujúci skin!");
    }
    $this->settingsStorage->write('/settings/skin/name', $newSkinName);
  }
}
