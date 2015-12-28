<?php

/**
 * This file contains tests for ais2 Table class.
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

use \PHPUnit_Framework_TestCase;
use fajr\libfajr\data_manipulation\DataTable;
use fajr\libfajr\data_manipulation\InformacnyListParser;
use fajr\libfajr\data_manipulation\InformacnyListAttributeEnum;
use fajr\libfajr\pub\base\NullTrace;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class InformacnyListParserTest extends PHPUnit_Framework_TestCase {

    private $html;

    public function setUp() {
        $this->html = file_get_contents(__DIR__ . '/testdata/rozsirenyInformacnyListSGarantomPredmetu.dat');
        $this->parser = new InformacnyListParser();
    }

    public function testInformacneListyParsing() {
        $table = $this->parser->parse(new NullTrace(), $this->html);
        $pomocna_premenna_pre_literaturu = $table->getAttribute(InformacnyListAttributeEnum::LITERATURA);
        $this->assertEquals(
                array(
                    'Univerzita Komenského v Bratislave - Fakulta matematiky, fyziky a informatiky',
                    'FMFI.KI/2-INF-235/00',
                    'Kryptológia (2)',
                    'mINF, mINF/k',
                    '',
                    '',
                    '4',
                    'Kurz',
                    '4',
                    '56',
                    '6',
                    '',
                    '1-INF-640 Kryptológia (1)',
                    'Hodnotenie',
                    '',
                    'skúška',
                    'Prezentovať zložitejšie kryptografické konštrukcie s použitím základných prvkov ako aj prístupy k formálnemu zdôvodneniu ich bezpečnosti.',
                    'Schémy na zdieľanie tajomstva, podpisové schémy s dodatočnými vlastnosťami, formálna analýza autentizačných protokolov, bezznalostné dokazovacie systémy, primitíva protokolov s viacerými účastníkmi (oblivious transfer, bit commitment), digitálne peniaze, elektronické voľby, dokázateľná bezpečnosť schém pre asymetrické šifrovanie a digitálne podpisy.',
                    'Stinson D.: Cryptography: Theory and Practice, 3rd Edition, CRC Press, 2005.',
                    'Menezes A.J., Van Oorschot P.C.: Handbook of Applied Cryptography, CRC Press, 1996.',
                    'Koblitz N.: A Course in Number Theory and Cryptography, Springer-Verlag, 1987.',
                    'Mao W.: Modern Cryptography: Theory and Practice, Hewlett-Packard, 2003.',
                    'anglický, slovenský',
                    '14.08.2009'
                ),
                array(
                    $table->getAttribute(InformacnyListAttributeEnum::SKOLA_FAKULTA),
                    $table->getAttribute(InformacnyListAttributeEnum::KOD),
                    $table->getAttribute(InformacnyListAttributeEnum::NAZOV),
                    $table->getAttribute(InformacnyListAttributeEnum::STUDIJNY_PROGRAM),
                    $table->getAttribute(InformacnyListAttributeEnum::GARANTUJE),
                    $table->getAttribute(InformacnyListAttributeEnum::ZABEZPECUJE),
                    $table->getAttribute(InformacnyListAttributeEnum::OBDOBIE_STUDIA_PREDMETU),
                    $table->getAttribute(InformacnyListAttributeEnum::FORMA_VYUCBY),
                    $table->getAttribute(InformacnyListAttributeEnum::VYUCBA_TYZDENNE),
                    $table->getAttribute(InformacnyListAttributeEnum::VYUCBA_SPOLU),
                    $table->getAttribute(InformacnyListAttributeEnum::POCET_KREDITOV),
                    $table->getAttribute(InformacnyListAttributeEnum::PODMIENUJUCE_PREDMETY),
                    $table->getAttribute(InformacnyListAttributeEnum::OBSAHOVA_PREREKVIZITA),
                    $table->getAttribute(InformacnyListAttributeEnum::SPOSOB_HODNOTENIA_A_SKONCENIA),
                    $table->getAttribute(InformacnyListAttributeEnum::PRIEBEZNE_HODNOTENIE),
                    $table->getAttribute(InformacnyListAttributeEnum::ZAVERECNE_HODNOTENIE),
                    $table->getAttribute(InformacnyListAttributeEnum::CIEL_PREDMETU),
                    $table->getAttribute(InformacnyListAttributeEnum::OSNOVA_PREDMETU),
                    $pomocna_premenna_pre_literaturu[0],
                    $pomocna_premenna_pre_literaturu[1],
                    $pomocna_premenna_pre_literaturu[2],
                    $pomocna_premenna_pre_literaturu[3],
                    $table->getAttribute(InformacnyListAttributeEnum::VYUCOVACI_JAZYK),
                    $table->getAttribute(InformacnyListAttributeEnum::DATUM_POSLEDNEJ_UPRAVY)
                )
        );
    }

}