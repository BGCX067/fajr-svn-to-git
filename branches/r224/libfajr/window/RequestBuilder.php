<?php
/* {{{
Copyright (c) 2010 Peter Peresini

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */
namespace AIS2;

interface RequestBuilder {
  /**
   * Vygeneruje dáta na POST request
   * @param string $dlgName názov aktuálneho dialógu
   * @param array() $options
   * @return array() POST data array
   */
  public function buildRequestData($dlgName, array $options);

  /**
   * Vygeneruje url na ktorú treba robiť request
   * @param string $appId id AIS aplikácie
   * @return string url
   */
  public function getRequestUrl($appId);

  /**
   * S každým requestom je treba posielať nový serial.
   * @return int aktuálny serial pre AIS.
   */
  public function newSerial();
}

