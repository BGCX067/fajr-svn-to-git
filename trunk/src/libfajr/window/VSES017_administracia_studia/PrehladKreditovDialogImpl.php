<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\VSES017_administracia_studia\PrehladKreditovDialog;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\window\AIS2AbstractDialog;
use fajr\libfajr\data_manipulation\AIS2TableParser;
use fajr\libfajr\window\DialogParent;
use fajr\libfajr\window\DialogData;

/**
 * Trieda pre dialóg s prehľadom kreditov
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class PrehladKreditovDialogImpl extends AIS2AbstractDialog
    implements PrehladKreditovDialog
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, DialogParent $parent,
      DialogData $data, AIS2TableParser $parser = null)
  {
    parent::__construct($trace, $parent, $data);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }

  public function getPredmety(Trace $trace)
  {
  $this->openIfNotAlready($trace);
    $response = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $response,
        'predmetyTable_dataView');
  }
}
