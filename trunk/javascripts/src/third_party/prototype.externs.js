/**
 * @fileoverview
 *
 * Definition of external methods provided by prototype.js
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Peter Perešíni<ppershing+fajr@gmail.com>
 * @externs
 */

// Warning: this section is really incomplete,
// if you use something new from prototype api,
// please export it here also.

Element.prototype.each = function(callback) {}
Element.prototype.addClassName = function(className) {}
Element.prototype.removeClassName = function(className) {}
Element.prototype.hasClassName = function(className) {}
Element.prototype.invoke = function(method, param) {}
Element.prototype.up = function() {}
Function.curry = function() {}

/**
 * @param {string} p
 * @returns {Element}
 */
function $$(p){};

/**
 * @param {string|Element} id
 * @returns {Element}
 */
function $(id){};

Element.observe = function(eventName, handler) {};

document.observe = Element.observe;
