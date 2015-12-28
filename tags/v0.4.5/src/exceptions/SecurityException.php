<?php
/**
 * Contains exception used for signalling security violation.
 * This exception is usually handled in specific way (log info to file, ...)
 *
 * @copyright  Copyright (c) 2010 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\exceptions;

use Exception;

/**
 * This exception is thrown when system detects tampering with
 * its data. The sources are mainly tampering with GET parameters,
 * POST data, cookies, ...
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class SecurityException extends Exception
{

}
