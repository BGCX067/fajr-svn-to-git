/**
 * @fileowerview
 * Placeholder for 'provides' of all third_party libraries.
 *
 * Purpose of this file is to provide neccessary
 * 'provides' for all used third_party libraries.
 * We do not want to place these provides in the libraries because
 * a) libraries are loaded separately (mainly for mainternance
 *    and reusal reasons)
 * b) we do not want to compile libraries with js compiler
 *    (at least not want to show all the warnings from them)
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Javascripts
 * @author     Peter Perešíni<ppershing+fajr@gmail.com>
 */
goog.provide('third_party.latinise');
goog.provide('third_party.prototype');
goog.provide('third_party.tablesort');
goog.provide('third_party.google_analytics');
