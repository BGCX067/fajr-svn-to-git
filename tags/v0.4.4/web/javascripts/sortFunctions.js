// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

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

function sortPrihlasovanieOdhlasovaniePrepareData(tdNode, innerText)
{
  var m = innerText.match(/do (\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{1,2}):(\d{2})$/);
  if (!m) {
    return -1;
  }
  // Vratime string tvaru YYYYMMDDhhmm
  return pad0(m[3], 4) + pad0(m[2], 2) + pad0(m[1], 2) +
         pad0(m[4], 2) + pad0(m[5], 2);
}

function sortPrihlasovanieOdhlasovanie(a, b)
{
  var aa = a[fdTableSort.pos];
  var bb = b[fdTableSort.pos];
  if(aa == bb) return 0;
  if(aa < bb)  return -1;
  return 1;
}

function sortDatumCasPrepareData(tdNode, innerText)
{
  var m = innerText.match(/(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{1,2}):(\d{2}):(\d{2})$/);
  if (!m) {
    return -1;
  }
  // Vratime string tvaru YYYYMMDDhhmmss
  return pad0(m[3], 4) + pad0(m[2], 2) + pad0(m[1], 2) +
         pad0(m[4], 2) + pad0(m[5], 2) + pad0(m[6], 2);
}

function sortDatumCas(a, b)
{
  var aa = a[fdTableSort.pos];
  var bb = b[fdTableSort.pos];
  if(aa == bb) return 0;
  if(aa < bb)  return -1;
  return 1;
}