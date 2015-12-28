<?php
/**
 * This file sets assert handler
 *
 * PHP version 5.2.6
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Peresini <ppershing+fajr@google.com>
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
 * @author     Peter Peresini <ppershing+fajr@google.com>
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
     * @return void
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
     * @return void
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
