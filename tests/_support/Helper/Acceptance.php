<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
	/**
	 * Same as assertRegExp but makes it available inside AcceptanceTester
	 * (not sure why it isn't anyway... there might be a better way).
	 */
	public function doAssertRegExp($regexp, $text)
	{
		return $this->assertRegExp($regexp, $text);
	}

	public function formattedDateInRange($min, $max, $format, $actual)
	{
		$date = \DateTime::createFromFormat($format, $actual, new \DateTimeZone("Europe/Berlin"));
		$this->assertGreaterThan($min, $date, 'Date is in past');
		$this->assertLessThan($max, $date, 'Date is in future');
	}

	public function seeFileExists($filename)
	{
		\PHPUnit_Framework_Assert::assertTrue( file_exists($filename) );
	}
}
