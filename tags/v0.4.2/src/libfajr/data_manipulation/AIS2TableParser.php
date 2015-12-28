<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains implementation of ais2 table parsing from
 * html response.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use DOMDocument;
use DOMElement;
use DOMXPath;
use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\exceptions\ParseException;

/**
 * Parses AIS2 html response and retrieve data for specific table.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 *
 * TODO(ppershing): document methods
 */
class AIS2TableParser
{
  public function fixProblematicTags(Trace $trace, $html)
  {
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);
    $html = str_replace("script", "div", $html);
    $trace->tlogVariable("Fixed html", $html);
    return $html;
  }

  public function fixIdAttributes(Trace $trace, DOMDocument $dom)
  {
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//*[@id]");
    foreach ($nodes as $node) {
      // Note: do not erase next line. @see
      // http://www.navioo.com/php/docs/function.dom-domelement-setidattribute.php
      // for explanation!
      $node->setIdAttribute('id', false);
      $node->setIdAttribute('id', true);
    }
  }

  public function createDomFromHtml(Trace $trace, $html)
  {
    $dom = new DOMDocument();
    $trace->tlog("Loading html to DOM");
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new ParseException("Problem parsing html to DOM.");
    }
    $trace->tlog('Fixing id attributes in the DOM');
    $this->fixIdAttributes($trace, $dom);
    return $dom;
  }

  public function findEnclosingElement(Trace $trace, DOMDocument $dom, $elementId)
  {
    $trace->tlog("Finding element with id '$elementId'");
    $element = $dom->getElementById($elementId);
    if ($element === null) {
      throw new ParseException("Problem parsing ais2 response: Element not found");
    }
    $trace->tlog("Element found");
    $child = $trace->addChild("Element xml content (pretty formatted)");
    $child->tlogVariable("content", $this->prettyFormatXml($dom, $element));
    return $element;
  }

  public function prettyFormatXml($dom, $element)
  {
    $outXML = '<?xml version="1.0" encoding="UTF-8"?>'.$dom->saveXML($element);
    $tmp = new DOMDocument();
    $tmp->encoding='UTF-8';
    $tmp->preserveWhiteSpace = false;
    $tmp->formatOutput = true;
    $tmp->loadXML($outXML);
    return $tmp->saveXML(); 
  }

  public function createTableFromHtml(Trace $trace, $aisResponseHtml, $dataViewName)
  {
    $html = $this->fixProblematicTags($trace->addChild("Fixing html for better DOM parsing."),
                                                       $aisResponseHtml);
    $domWholeHtml = $this->createDomFromHtml($trace, $html);
    $element = $this->findEnclosingElement($trace, $domWholeHtml, $dataViewName);
    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document
    $headers = $this->getTableDefinition($trace->addChild("Get table definition"), $dom);
    $data = $this->getTableData($trace->addChild("Get table data"), $dom);
    return new DataTableImpl($headers, $data);
  }

  public function fixNbsp($str)
  {
    // special fix for &nbsp;
    // xml decoder decodes &nbsp; into special utf-8 character
    // TODO(ppershing): nehodili by sa tie &nbsp; niekedy dalej v aplikacii niekedy?
    $nbsp = chr(0xC2).chr(0xA0);
    return str_replace($nbsp, ' ', $str);
  }

  public function getCellContent(DOMElement $element)
  {
    // special fix for checkboxes
    if ($element->hasAttribute('datatype')) {
      switch ($element->getAttribute('datatype')) {
        case 'image':
          assert($element->getAttribute('datatype') == 'image');
          foreach ($element->getElementsByTagName('img') as $img) {
            assert($img->hasAttribute('src'));
            $src = $img->getAttribute('src');
            if (preg_match('@checked.gif@', $src)) return "TRUE";
            if (preg_match('@removeFlag.gif@', $src)) return "FALSE";
            assert(false);
          }
          assert(false);
        case 'boolean':
          foreach($element->getElementsByTagName('img') as $img) {
            assert($img->hasAttribute('class'));
            assert($img->getAttribute('class') === 'checkedImg');
            return "TRUE";
          }
          assert($this->fixNbsp($element->textContent) === ' ');
          return 'FALSE';
        default:
        assert(false);
      }
    }
    $value = $this->fixNbsp($element->textContent);

    if ($value == ' ') {
      return '';
    }
    assert($value != ''); // probably there is some inner element which we don't know about
    return $value;
  }

  public function getTableData(Trace $trace, $dom)
  {
    $data = array();
    $trace->tlog("finding tbody element");
    $element = $dom->getElementById('dataTabBody0');
    if ($element == null) {
      throw new ParseException("Can't find table data");
    }

    foreach ($element->childNodes as $aisRow) {
      assert($aisRow->tagName == "tr");
      assert($aisRow->hasAttribute("id"));
      assert($aisRow->hasChildNodes());
      // TODO: asserty prerobit na exceptiony
      $row = array();
      $rowId = $aisRow->getAttribute("id");
      $index = match($rowId, '@^row_([0-9]+)$@');
      if ($index === false) {
        throw new ParseException("Unexpected row id format");
      }
      foreach ($aisRow->childNodes as $ais_td) {
        assert($ais_td->tagName == "td");
        $row[] = $this->getCellContent($ais_td);
      }
      $data[$index] = $row;
    }
    $trace->tlogVariable("data", $data);
    return $data;
  }

  public function getTableDefinition(Trace $trace, DOMDocument $dom)
  {
    $trace->tlog("finding table definition element");
    $trace->tlogVariable("", $dom->saveXML());
    $element = $dom->getElementById('dataTabColGroup');
    if ($element == null) {
      throw new ParseException("Can't find table headers");
    }
    $list = $element->getElementsByTagName('col');
    $columns = array();
    foreach ($list as $node) {
      assert($node->hasAttribute('shortname'));
      $columns[] = $node->getAttribute('shortname');
    }
    $trace->tlogVariable("Parsed columns:", $columns);
    return $columns;
  }

}
