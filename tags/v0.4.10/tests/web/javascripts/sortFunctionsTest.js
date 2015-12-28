//JsTestDriver:use-UTF-8-compatibility-hack
/**
 * Contains tests of sortFunctions.js
 *
 * @copyright  Copyright (c) 2010 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Web__Javascripts
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

SortFunctionsTest = TestCase("SortFunctionsTest");

SortFunctionsTest.prototype.setUp = function() {
  if (typeof(sortFunctionsLoaded) == 'undefined') {
    fail("sortFunctions.js should be loaded");
  }
  if (typeof(latiniseLoaded) == 'undefined') {
    fail("latinise.js should be loaded");
  }
}

/**
 * Test basic padding.
 */
SortFunctionsTest.prototype.testPad0Basic = function() {
  expectAsserts(4);
  assertEquals("12", pad0("12", 1));
  assertEquals("12", pad0("12", 2));
  assertEquals("012", pad0("12", 3));
  assertEquals("0012", pad0("12", 4));
}

/**
 * Test some tricky cases.
 */
SortFunctionsTest.prototype.testPad0Tricky = function() {
  expectAsserts(4);
  assertEquals("0000", pad0("", 4));
  assertEquals("00 0", pad0(" 0", 4));
  assertEquals("00  ", pad0("  ", 4));
  assertEquals("  ", pad0("  ", -4));
}

/**
 * Test parsing date and time
 */
SortFunctionsTest.prototype.testParseDatumCas = function() {
  expectAsserts(3);
  assertEquals(new Date(2011, 1, 11, 20, 00, 00),
               parseDatumCas("11.01.2011 20:00"));
  assertEquals(new Date(2010, 11, 22, 17, 34, 22),
               parseDatumCas("22.11.2010 17:34:22"));
  assertException(function() {parseDatumCas("wrong")}, "Error");
}


SortFunctionsTest.prototype.testNormalizePriezviskoMeno = function() {
  expectAsserts(1);
  assertEquals("brejova bronislava",
      normalizePriezviskoMenoForCmp("Mgr. Bronislava Brejová, PhD."));
}

/**
 * Test sorting names
 */
SortFunctionsTest.prototype.testSortPriezviskoMeno = function() {
  var testdata = [
      "Mgr. Bronislava Brejová, PhD.",
      "doc. RNDr. Pavol Ďuriš, CSc.",
      "RNDr. Michal Forišek, PhD.",
      "Ing. Janko Hraško",
      "Martinko Klingáč",
      "doc. RNDr. Rastislav Královič, PhD.",
      "doc. RNDr. Martin Mačaj, PhD.",
      "Bc. Peter Perešíni",
      "Mgr. Milan Plžík",
      "prof. RNDr. Branislav Rovan, PhD.",
      "prof. RNDr. Martin Škoviera, PhD.",
      "doc. RNDr. Martin Stanek, PhD.",
      "RNDr. Ján Šturc, CSc.",
      "doc. RNDr. Eduard Toman, CSc." // ppershing: comma here breaks ie
      ];
  for (var i = 0; i < testdata.length; i++) {
    for (var j = i+1; j < testdata.length; j++) {
      var a = normalizePriezviskoMenoForCmp(testdata[i]);
      var b = normalizePriezviskoMenoForCmp(testdata[j]);
      assertTrue(a + " ma byt menej ako " + b, a < b);
    }
  }
  
}
