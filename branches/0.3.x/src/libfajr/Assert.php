<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file sets assert handler
 *
 * PHP version 5.2.6
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr;

use fajr\libfajr\util\CodeSnippet;
use \Exception;
// We can't depend on autoloader here!
require_once 'util/CodeSnippet.php';

/**
 * static class containing assert callback
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Assert
{
    /**
     * Means how many lines from original assertion that failed will
     * be shown in a log.
     */
    const NEAR_CODE = 3;

    /**
     * assert handler
     *
     * @param string $file filename 
     * @param int    $line line number
     * @param string $code code that failed
     *
     * @returns void
     * @throws Exception each time called new Exception is thrown
     */
    public static function myAssertHandler($file, $line, $code)
    {
      $fileDump = CodeSnippet::getCodeSnippet($file, $line, self::NEAR_CODE);
      $message  = "Assertion failed on line $file: $line,code:\n\n$fileDump";
      $message .= "\nFailed expression: ";
      $message .= var_export($code, true);
      $message .= "\n";

      throw new Exception($message);
    }

    /**
     * Register assertion handler in php
     *
     * @returns void
     */
    public static function register()
    {
      assert_options(ASSERT_ACTIVE, 1);
      assert_options(ASSERT_WARNING, 0);
      assert_options(ASSERT_QUIET_EVAL, 1);
      assert_options(ASSERT_CALLBACK, array('fajr\libfajr\Assert', 'myAssertHandler'));
    }
}

Assert::register();
