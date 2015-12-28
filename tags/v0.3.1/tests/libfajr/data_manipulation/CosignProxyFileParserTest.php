<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for Cosign proxy file parser
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\data_manipulation\CosignProxyFileParser;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\login\CosignServiceCookie;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignProxyFileParserTest extends PHPUnit_Framework_TestCase
{
  private $parser;

  public function setUp()
  {
    $this->parser = new CosignProxyFileParser();
  }

  public function testProxyFileParsing()
  {
    $this->assertEquals($this->parser->parseFile(new NullTrace(),
        __DIR__.'/testdata/cosignProxyFile.dat'),
        array('cosign-groupware'=>new CosignServiceCookie('cosign-groupware',
              'qsZJi0nbBZfbJfDZ3dJ7J-5yqsEjON+DtqOHYnMEPTQCT7vzeomMO+CmkWV+w1aPk0gdxaIV-ucVPvG6lA4UYoGAbVDWrYK1UZCfbmzMQJH1QAH1W4+ieYUF89bV',
              'groupware.cosign.test')
          ));
  }

}
