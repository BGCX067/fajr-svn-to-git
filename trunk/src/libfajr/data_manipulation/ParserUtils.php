<?php

// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains utils on parsing html files.
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\data_manipulation;

use DOMDocument;
use DOMElement;
use DOMXPath;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\pub\base\Trace;


/**
 * Contains utils on parsing html files.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Jakub Marek <jakub.marek@gmail.com>
 *
 */
class ParserUtils {

    /**
     * Fix problem with PHP DOM "id" attribute parsing.
     *
     * Sometimes "id" attribute is not recognized as id attribute during parsing.
     * This method will fix the problem.
     *
     * @param Trace $trace
     * @param DOMDocument $dom DOM document to be fixed
     *
     * @returns void
     */
    public static function fixIdAttributes(Trace $trace, DOMDocument $dom) {
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

    /**
     * Fix non-breakable spaces which were converted to special character during parsing.
     *
     * @param string $str string to fix
     *
     * @returns string fixed string
     */
    public static function fixNbsp($str) {
        Preconditions::checkIsString($str);
        // special fix for &nbsp;
        // xml decoder decodes &nbsp; into special utf-8 character
        // TODO(ppershing): nehodili by sa tie &nbsp; niekedy dalej v aplikacii niekedy?
        $nbsp = chr(0xC2) . chr(0xA0);
        return str_replace($nbsp, '', $str);
    }

    /**
     * Parses ais html into DOM.
     *
     * @param Trace $trace
     * @param string $html
     *
     * @returns DOMDocument parsed DOM
     * @throws ParseException on failure
     */
    public static function createDomFromHtml(Trace $trace, $html) {
        Preconditions::checkIsString($html);
        $dom = new DOMDocument();
        $trace->tlog("Loading html to DOM");
        $loaded = @$dom->loadHTML($html);
        if (!$loaded) {
            throw new ParseException("Problem parsing html to DOM.");
        }
        $trace->tlog('Fixing id attributes in the DOM');    
        ParserUtils::fixIdAttributes($trace, $dom);
        return $dom;
    }

}
