<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Application availability parser for AIS html pages.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\util\StrUtil;
use fajr\libfajr\base\Preconditions;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Parses AIS2 html response and finds whether specific application is available.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2ApplicationAvailabilityParser {

  public function createDomFromHtml($html)
  {
    Preconditions::checkIsString($html);
    $dom = new DOMDocument();
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new ParseException("Problem parsing html to DOM.");
    }
    return $dom;
  }

  public function getApplication(DOMElement $element)
  {
    foreach ($element->getElementsByTagName('div') as $subelement) {
      if ($subelement->hasAttribute("class") &&
          $subelement->getAttribute("class") == "kod") {
        return $subelement->textContent;
      }
    }
    throw new ParseException("Can't find application code in html");
  }

  public function getAllApplications(DOMDocument $dom)
  {
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//li[@class='aplikacia']/div[@class='clear']");
    $result = array();
    foreach ($nodes as $node) {
      $result[] = $this->getApplication($node);
    }
    return $result;
  }

  public function findAllApplications($html)
  {
    Preconditions::checkIsString($html);
    $dom = $this->createDomFromHtml($html);
    return $this->getAllApplications($dom);
  }
}
