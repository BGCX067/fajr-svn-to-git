<?php
/**
 * This file contains tests for AIS2 utility class
 *
 * @package libfajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class AIS2UtilsTest extends PHPUnit_Framework_TestCase {

public function testParseAISDateTime() {
	$this->assertEquals(AIS2Utils::parseAISDateTime("11.01.2010 08:30"),
			    mktime(8, 30, 00, 1, 11, 2010));
	$this->assertEquals(AIS2Utils::parseAISDateTime("31.02.2011 09:35"),
			    mktime(9, 35, 00, 2, 31, 2011));

	// parsovanie bez uvodnych nul zatial nepodporujeme (a ani nemusime)
	// AIS2Utils::parseAISDateTime("31.2.2011 9:35");

	$this->setExpectedException("Exception");
	AIS2Utils::parseAISDateTime("8:30 11.1.2010");
}

public function testDateTimeRange() {
	$this->assertEquals(AIS2Utils::parseAISDateTimeRange(
				"11.01.2010 08:30 do 12.02.2011 09:40"),
			array('od' => mktime(8,30,0, 1,11,2010),
				'do' => mktime(9,40,0, 2,12,2011)));


}
}

?>