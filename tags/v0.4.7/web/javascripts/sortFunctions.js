// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/** @require latinise.js */

/**
 * Dopln nuly na zaciatok retazca, az do dlzky n
 */
function pad0(s, n)
{
  while (s.length < n) {
    s = '0' + s;
  }
  return s;
}

/**
 * Parse date and time in slovak format.
 *
 * @param {string} text date and time in format "21.01.2011 20:00[:00]"
 *
 * @returns {Date} parsed date
 */
function parseDatumCas(text)
{
  var m = text.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{1,2}):(\d{2}):(\d{2})$/);
  if (m) {
    return new Date(m[3], m[2], m[1], m[4], m[5], m[6]);
  }
  
  m = text.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{1,2}):(\d{2})$/);
  if (m) {
    return new Date(m[3], m[2], m[1], m[4], m[5], 0);
  }

  throw Error("Problem parsing datetime");
}

/**
 * Sort by position generated during prepareData call
 * of unobtrusive table sort
 */
function sortByPreparedPosition(a, b)
{
  var aa = a[fdTableSort.pos];
  var bb = b[fdTableSort.pos];
  if(aa == bb) return 0;
  if(aa < bb)  return -1;
  return 1;
}

function sortPrihlasovanieOdhlasovaniePrepareData(tdNode, innerText)
{
  var m = innerText.match(/do (.*)$/);
  if (!m) {
    return -1;
  }
  try {
    return parseDatumCas(m[1]);
  } catch (err) {
    return -1;
  }
}

function sortPrihlasovanieOdhlasovanie(a, b)
{
  return sortByPreparedPosition(a, b);
}

function sortDatumCasPrepareData(tdNode, innerText)
{
  try {
    return parseDatumCas(innerText);
  } catch (err) {
    return -1;
  }
}

function sortDatumCas(a, b)
{
  return sortByPreparedPosition(a, b);
}

function normalizePriezviskoMenoForCmp(text) 
{
  var tituly = ["Bc.", "Mgr.", "Ing.", "PhD.", "CSc.", "doc.", "prof.", "RNDr.",
    // a este pridame exotickejsie, list z wikipedie
    "ThLic.", "ThDr.", "ArtD.", "ThLic.", "MBA", "ThMgr.", "PhMr.",
    "MVc.", "MUC.", "RSDr.", "RCDr.", "ICDr.", "akad.",
    "MVDr.", "MDDr.", "MUDr.", "arch.", "PaeDr.", "JUDr.", "PhDr.",
    "PharmDr.", "art."
  ];
  // Example meno: "doc. RNDr. Rastislav Královič, PhD."

  var m = text.split(" ");
  // zbavime sa ciarok za menom
  m = m.map(function(x) {
              return x.replace(/,/, "");
            });
  // odstran vsetky tituly
  m = m.filter(function(x) {
                  return -1 == tituly.indexOf(x);
                });
  // krstne meno hod na koniec
  var krstne = m.shift();
  m.push(krstne);
  // a cele to zabal naspat
  var result = m.join(" ");
  result = result.toLowerCase();
  return result.latinise();
}

function sortPriezviskoMenoPrepareData(tdNode, innerText)
{
  return normalizePriezviskoMenoForCmp(innerText);
}

function sortPriezviskoMeno(a, b)
{
  return sortByPreparedPosition(a, b);
}

var sortFunctionsLoaded = true;
