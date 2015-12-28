<?php

/**
 * Contains implementation of parsing of information lists.
 *
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
use fajr\libfajr\data_manipulation\DataTableImpl;
use fajr\libfajr\data_manipulation\InformacnyListAttributeEnum;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\exceptions\ParseException;
use fajr\libfajr\data_manipulation\ParserUtils;

/**
 * Parses AIS2 information list and retrieves data.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Jakub Marek <jakub.marek@gmail.com>
 *
 */
class InformacnyListParser {

    private $list;
    private $attribute_names;

    /**
     * Parses html document into object InformationListDataImpl which
     * handles all further manipulation with information list.
     *
     * @param string $html
     *
     * @return new instance of class InformacnyListDataImpl
     *
     */
    public function parse(Trace $trace, $html) {
        $trace->tlog("Called method parse(), creating table with parsed elements");
        $table = $this->parseHtmlIntoTable($trace, $html);
        $this->checkIntegrityOfAttributes($trace, $this->attribute_names);

        $this->list = array();
        $trace->tlog("Created new instance of InformacnyListDataImpl object");
        $info = array(
            InformacnyListAttributeEnum::SKOLA_FAKULTA => $table[0],
            InformacnyListAttributeEnum::KOD => $table[1],
            InformacnyListAttributeEnum::NAZOV => $table[2],
            InformacnyListAttributeEnum::STUDIJNY_PROGRAM => $table[3],
            InformacnyListAttributeEnum::GARANTUJE => $table[4],
            InformacnyListAttributeEnum::ZABEZPECUJE => $table[5],
            InformacnyListAttributeEnum::OBDOBIE_STUDIA_PREDMETU => $table[6],
            InformacnyListAttributeEnum::FORMA_VYUCBY => $table[7],
            //cislo 8 je tag <b> ktory za sebou nema ziadnu informaciu, funguje ako podnadpis
            // ale sparsuje sa, ten nechceme
            InformacnyListAttributeEnum::VYUCBA_TYZDENNE => $table[9],
            InformacnyListAttributeEnum::VYUCBA_SPOLU => $table[10],
            InformacnyListAttributeEnum::POCET_KREDITOV => $table[11],
            InformacnyListAttributeEnum::PODMIENUJUCE_PREDMETY => $table[12],
            InformacnyListAttributeEnum::OBSAHOVA_PREREKVIZITA => $table[13],
            InformacnyListAttributeEnum::SPOSOB_HODNOTENIA_A_SKONCENIA => $table[14],
            InformacnyListAttributeEnum::PRIEBEZNE_HODNOTENIE => $table[15],
            InformacnyListAttributeEnum::ZAVERECNE_HODNOTENIE => $table[16],
            InformacnyListAttributeEnum::CIEL_PREDMETU => $table[17],
            InformacnyListAttributeEnum::OSNOVA_PREDMETU => $table[18],
            InformacnyListAttributeEnum::LITERATURA => $table[19],
            InformacnyListAttributeEnum::VYUCOVACI_JAZYK => $table[20],
            InformacnyListAttributeEnum::DATUM_POSLEDNEJ_UPRAVY => $table[21]
        );
        foreach ($info as $attribute => $value) {
            $this->setAttribute($trace, $attribute, $value);
        }
        $trace->tlogVariable("parsed list", $this->list);
        $new_list = new InformacnyListDataImpl($this->list);
        return $new_list;
    }

    /**
     * Sets value for attribute in associative array $list.
     *
     * @param string $attribute
     * @param string|array $value
     *
     */
    public function setAttribute(Trace $trace, $attribute, $value) {
        $trace->tlog("Setting value: '$value' as attribute: '$attribute' into list");
        if (is_array($value)) {
            if (count($value) > 1) {
                for ($i = 0; $i < count($value); $i++) {
                    $this->list[$attribute][] = self::trimDeleteNewline($value[$i]);
                }
            } else {
                $this->list[$attribute] = self::trimDeleteNewline(end($value));
            }
        } else {
            $this->list[$attribute] = self::trimDeleteNewline($value);
        }
    }

    /**
     * Deletes breaklines and trims string.
     *
     * @param string $string
     *
     * @return trimmed string without brakelines
     *
     */
    static function trimDeleteNewline($string) {
        Preconditions::checkIsString($string);
        return trim(str_replace(array("\r", "\r\n", "\n"), '', $string));
    }

    /**
     * Checks if all parsed attributes belong to correct <b> nodes.
     *
     * @param array $table
     *
     * @returns boolean
     *
     * @throws ParseException
     */
    public function checkIntegrityOfAttributes(Trace $trace, $table) {
        $expected = array(
            'Názov vysokej školy, názov fakulty:',
            'Kód:',
            'Názov:',
            'Študijný program:',
            'Garantuje:',
            'Zabezpečuje:',
            'Obdobie štúdia predmetu:',
            'Forma výučby:',
            'Odporúčaný rozsah výučby ( v hodinách ):',
            'Týždenný:',
            'Za obdobie štúdia:',
            'Počet kreditov:',
            'Podmieňujúce predmety:',
            'Obsahová prerekvizita:',
            'Spôsob hodnotenia a skončenia štúdia predmetu:',
            'Priebežné hodnotenie (napr. test, samostatná práca...):',
            'Záverečné hodnotenie (napr. skúška, záverečná práca...):',
            'Cieľ predmetu:',
            'Stručná osnova predmetu:',
            'Literatúra:',
            'Jazyk, v ktorom sa predmet vyučuje:',
            'Podpis garanta a dátum poslednej úpravy listu:'
        );
        if ($expected != $table) {
            throw new ParseException("Attributes inconsistent.");
            $trace->tlog("Integrity test of parsed attributes failed.");
            return false;
        }
        return true;
    }

    /**
     * Parses <b> element, as after <b> elements occur data, that needs to
     * be extracted.
     *
     * @param DOMElement $final
     * @param array $pole
     *
     * @returns array with parsed data
     */
    private function spracujB(Trace $trace, DOMElement $final) {
        $pole = array();
        //do attribue_names pridam element, podla ktoreho parsujem
        $this->attribute_names[] = ParserUtils::fixNbsp($final->nodeValue);
        $child = $trace->addChild("Parsing tag '$final->nodeValue'");
               
        $sused = $final->nextSibling;
        if ($sused == NULL) {
            $child->tlog("Only element to parse");
            $child->tlogVariable("Parsed attribute:", '');
            return array('');
        }
        if ($sused->nextSibling == NULL) {
            // je textNode
            $child->tlog("Attribute is text node");
            $child->tlogVariable("Parsed attribute:", $sused->nodeValue);
            return array(ParserUtils::fixNbsp($sused->nodeValue));
        }
        $text_sused = $sused->nextSibling;
        if ($text_sused->nodeType != \XML_ELEMENT_NODE) {
            $child->tlog("Nothing to parse here");
            return array();
        }
        if ($text_sused->tagName == 'p') {
            $child->tlog("Parsing <p> tags");
            while ($text_sused != NULL) {
                if ($text_sused->nodeType != \XML_ELEMENT_NODE) {
                    $text_sused = $text_sused->nextSibling;
                    continue;
                }
                if ($text_sused->tagName == 'p') {
                    $child->tlogVariable("Parsed attribute:", $sused->nodeValue);
                    $pole[0][] = ParserUtils::fixNbsp($text_sused->nodeValue);
                }
                $text_sused = $text_sused->nextSibling;
            }
        } else {
            $child->tlog("Parsing other tags");
            $child->tlogVariable("Parsed attribute:", $sused->nodeValue);
            $pole[] = ParserUtils::fixNbsp($sused->nodeValue);
        }
        return $pole;
    }

    /**
     * Replaces <br> tags in html document, so they wont complicate
     * further parsing.
     *
     * @param string $html html code to fix
     *
     * @returns string fixed html code ready for DOM parsing.
     */
    public static function fixBr(Trace $trace, $html) {
        Preconditions::checkIsString($html);
        $html = str_replace("<br>", "", $html);
        return $html;
    }

    /**
     * Creates array with elements parsed from html containing information list.
     *
     * @param string $aisResponseHtml
     *
     * @returns complete array with parsed data from html
     * @throws ParseException on failure of creating DOM from html
     */
    public function parseHtmlIntoTable(Trace $trace, $aisResponseHtml) {
        $parsedData = array();
        $this->attribute_names = array();

        Preconditions::checkIsString($aisResponseHtml);
        $html = self::fixBr($trace, $aisResponseHtml);
        $domWholeHtml = ParserUtils::createDomFromHtml($trace, $html);
        $domWholeHtml->preserveWhiteSpace = false;

        //ziskanie nazvu skoly, jedina vec co chcem ziskat co sa nenachadza v tabulke
        $b = $domWholeHtml->getElementsByTagName("b");
        $trace->tlog("Finding first element with tag name 'b'");
        $parsedData = $this->spracujB($trace, $b->item(0));

        $tr = $domWholeHtml->getElementsByTagName("tr");
        $trace->tlog("Getting all elements with tag name 'tr'");
        // prechadzam vsetkymi <tr> tagmi
        $firstTr = 0;
        foreach ($tr as $tr_key) {
            // nechcem uplne prvy tag co je v tr, za <b> je iba nazov: informacny list
            if ($firstTr == 0) {
                $firstTr = 1;
                continue;
            }
            $trace->tlog("Getting all elements with tag name 'td'");
            $td = $tr_key->getElementsByTagName("td");
            // prechadzam <td> tagmi
            foreach ($td as $td_key) {
                if (!$td_key->hasChildNodes()) {
                    continue;
                }
                $trace->tlog("Getting all child nodes of element 'td'");
                $td_children = $td_key->childNodes;
                foreach ($td_children as $final) {
                    if ($final->nodeType != \XML_ELEMENT_NODE) {
                        continue;
                    }
                    if ($final->tagName == 'b') {
                        $trace->tlog("Parsing node with tag name 'b'");
                        $parsedData = array_merge($parsedData, $this->spracujB($trace, $final));
                    }
                    if ($final->tagName == 'div') {
                        $trace->tlog("Parsing node with tag name 'div'");
                        $parsedData = array_merge($parsedData, $this->parseDiv($trace, $final));
                    }
                }
            }
        }
        return $parsedData;
    }

    /**
     * Parses div tag. If it contains <b> element, it calls method spracujB,
     * which parses element <b>.
     *
     * @param domNode $final
     *
     * @returns array
     */
    public function parseDiv(Trace $trace, $final) {
        $final2 = $final->childNodes;
        foreach ($final2 as $key) {
            if ($key->nodeType != \XML_ELEMENT_NODE) {
                continue;
            }
            if ($key->tagName == 'b') {
                $trace->tlog("Parsing node with tag name 'b' inside 'div' tag");
                $pole = $this->spracujB($trace, $key);
            }
        }
        return $pole;
    }

}
