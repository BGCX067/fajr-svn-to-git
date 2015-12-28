<?php
/**
 * Holds configuration about one skin.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\config;

use fajr\validators\ChoiceValidator;
use fajr\validators\StringValidator;
use fajr\util\FajrUtils;

/**
 * Contains all configurable options of Fajr skin.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class SkinConfig
{
  /** @var array(string=>mixed) */
  private $config;

  protected static function getParameterDescription()
  {
    $booleanValidator = new ChoiceValidator(array(true, false));
    $stringValidator = new StringValidator();
    $pathValidator = new StringValidator();

    return array(
        'name' =>
          array('validator' => $stringValidator),
        'internal' =>
          array('validator' => $booleanValidator,
                'defaultValue' => false),
        'parent' =>
          array('validator' => $stringValidator,
                'defaultValue' => null),
        'path' =>
          array('validator' => $pathValidator,
            ));
  }

  public function __construct(array $options)
  {
    $this->config = ConfigUtils::parseAndValidateConfiguration(
        $this->getParameterDescription(),
        $options);
  }

  public function getSkinName()
  {
    return $this->config['name'];
  }

  public function getParentName()
  {
    return $this->config['parent'];
  }

  public function getAllPaths() {
    $result = array($this->getPath());
    if ($this->getParentName() !== null) {
      $skins = FajrConfig::get('Template.Skin.Skins');
      $parent = $this->getParentName();
      if (!isset($skins[$parent])) {
        throw new RuntimeException("Parent skin '" . $parent . "' for '" .
            $this->getSkinName() . "' is not provided!");
      }
      $result = array_merge($result, $skins[$this->getParentName()]->getAllPaths());
    }
    return $result;
  }

  public function getPath()
  {
    $dir = $this->config['path'];
    if (FajrUtils::isAbsolutePath($dir)) {
      return $dir;
    }

    // default resolve relative to Template.Directory
    $relativeTo = FajrConfig::getDirectory('Template.Directory');

    return FajrUtils::joinPath($relativeTo, $dir);
  }

}
