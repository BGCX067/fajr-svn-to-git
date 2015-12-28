<?php
/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre predmety
 *
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace fajr\controller\predmety;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\libfajr\AIS2Utils;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\window\AIS2ApplicationEnum;
use fajr\libfajr\pub\window\VSST060_register_predmetov as VSST060;
use fajr\libfajr\pub\regression;
use fajr\Request;
use fajr\Response;
use fajr\Sorter;
use fajr\util\FajrUtils;

/**
 * Controller, ktory sa stara o register predmetov
 *
 * @package    Fajr
 * @subpackage Controller__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class PredmetyController extends BaseController
{
  // @private
  private $registerPredmetovScreen;

  private $factory;
  private $serverTime;

  public function __construct(VSST060\VSST060_Factory $factory, $serverTime)
  {
    $this->factory = $factory;
    $this->serverTime = $serverTime;
  }

  /**
   * Invoke an action given its name
   *
   * This function requests information necessary to operate on
   * VSST060 AIS application
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Context $context fajr context
   */
  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);

    $request = $context->getRequest();
    $response = $context->getResponse();
    $session = $context->getSessionStorage();
    Preconditions::checkNotNull($request);
    Preconditions::checkNotNull($response);
    Preconditions::checkNotNull($session);
    // check access to application
    $apps = $session->read('ais/aisApps');
    if (!is_array($apps)) {
      throw new Exception("Interná chyba - zoznam AIS aplikácii je nekorektný.");
    }
    if (!in_array(AIS2ApplicationEnum::REGISTER_PREDMETOV,
                  $apps)) {
      $response->setTemplate('predmety/notAvailable');
      return;
    }

    $screenFactory = $this->factory;
    $register = $screenFactory->newRegisterPredmetovScreen($trace);
    
    $this->registerPredmetovScreen = $register;

    parent::invokeAction($trace, $action, $context);
  }

  public function runInformacnyList(Trace $trace, Context $context) {
    $request = $context->getRequest();
    $response = $context->getResponse();

    $code = $request->getParameter('code');

    Preconditions::check(!empty($code), "Nezadaný kód predmetu!");

    $content = $this->registerPredmetovScreen->getInformacnyList($trace, $code);
    $content = iconv("Windows-1250", "UTF-8", $content);

    $response->setTemplate('predmety/informacnyList');
    $response->set('content', $content);
  }
}
