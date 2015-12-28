<?php
/**
 * Contains controller managing user preferences.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Settings
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\settings;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\libfajr\AIS2Utils;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\Request;
use fajr\Response;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\config\FajrConfig;
use fajr\settings\SkinSettings;

/**
 * Controller, which manages user settings.
 *
 * @package    Fajr
 * @subpackage Controller__Settings
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class UserSettingsController extends BaseController
{
  private $settingsStorage;
  private $config;

  public function __construct(sfStorage $settingsStorage, FajrConfig $config)
  {
    $this->settingsStorage = $settingsStorage;
    $this->config = $config;
  }

  public function runSettings(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $response->setTemplate('settings/settings');
  }

  public function runSkin(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();
    $skinSettings = new SkinSettings($this->config, $this->settingsStorage);

    // set skin
    if ($request->getParameter('skinSelect')) {
      $skinSettings->setUserSkinName($request->getParameter('skinSelect'));
      // apply the skin for current request(user skin may be applied before this function)
      $response->setSkin($skinSettings->getUserSkin());
    }

    $response->set('availableSkins', $skinSettings->getAvailableSkins());
    $response->set('currentSkin', $skinSettings->getUserSkinName());

    $response->setTemplate('settings/skin');
  }
}
