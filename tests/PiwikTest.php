<?php

require(__DIR__ . '/../src/Piwik.php');

use \VisualAppeal\Piwik;

class PiwikTest extends PHPUnit_Framework_TestCase
{
	const TEST_SITE_URL = 'http://demo.piwik.org/';

	const TEST_SITE_ID = 7;

	const TEST_TOKEN = '';

	/**
	 * Piwik api instance.
	 *
	 * @var \VisualAppeal\Piwik
	 */
	private $_piwik = null;

	protected function setUp()
	{
		$this->_piwik = new Piwik(self::TEST_SITE_URL, self::TEST_TOKEN, self::TEST_SITE_ID);
	}

	protected function tearDown()
	{
		unset($this->_piwik);
		$this->_piwik = null;
	}

	/**
	 * Test creation of class instance.
	 */
	public function testInit()
	{
		$this->assertInstanceOf('\VisualAppeal\Piwik', $this->_piwik);
	}

	/**
	 * Test the default api call.
	 */
	public function testDefaultCall()
	{
		$result = $this->_piwik->getVisits();

		$this->assertInternalType('int', $result);
		$this->assertEquals('', implode(',', $this->_piwik->getErrors()));
	}

	/**
	 * Test the result of a time range.
	 *
	 * @depends testDefaultCall
	 */
	public function testRangePeriod()
	{
		$this->_piwik->setPeriod(Piwik::PERIOD_RANGE);
		$this->_piwik->setRange(date('Y-m-d', time() - 3600 * 24), date('Y-m-d'));
		$result = $this->_piwik->getVisitsSummary();

		$this->assertInternalType('object', $result);
		$this->assertEquals('', implode(',', $this->_piwik->getErrors()));
	}

	/**
	 * Test the result of one day
	 *
	 * @depends testDefaultCall
	 */
	public function testDayPeriod()
	{
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setDate(date('Y-m-d', time() - 3600 * 24));
		$result = $this->_piwik->getVisitsSummary();

		$this->assertInternalType('object', $result);
		$this->assertEquals('', implode(',', $this->_piwik->getErrors()));
	}

	/**
	 * Test the result of multiple dates.
	 *
	 * @depends testDayPeriod
	 * @link https://github.com/VisualAppeal/Piwik-PHP-API/issues/14
	 */
	public function testMultipleDates()
	{
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setRange(date('Y-m-d', time() - 3600 * 24 * 6), date('Y-m-d'));
		$result = $this->_piwik->getVisitsSummary();

		$this->assertInternalType('object', $result);
		$this->assertEquals(7, count((array) $result));
		$this->assertEquals('', implode(',', $this->_piwik->getErrors()));
	}

	/**
	 * Test if all dates and ranges get the same result
	 *
	 * @depends testRangePeriod
	 * @depends testMultipleDates
	 */
	public function testDateEquals()
	{
		$date = date('Y-m-d', time() - 3600 * 24 * 7);

		// Range
		$this->_piwik->setPeriod(Piwik::PERIOD_RANGE);
		$this->_piwik->setRange($date, $date);

		$result1 = $this->_piwik->getVisits();
		$this->_piwik->reset();

		// Single date
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setDate($date);

		$result2 = $this->_piwik->getVisits();
		$this->_piwik->reset();

		// Multiple dates
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setRange($date, $date);

		$result3 = $this->_piwik->getVisits();
		$result3 = $result3->$date;
		$this->_piwik->reset();

		// Multiple dates with default range end
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setRange($date);

		$result4 = $this->_piwik->getVisits();
		$result4 = $result4->$date;
		$this->_piwik->reset();

		// previousX respectively lastX date
		$this->_piwik->setPeriod(Piwik::PERIOD_DAY);
		$this->_piwik->setRange('previous7');

		$result5 = $this->_piwik->getVisits();
		$result5 = $result5->$date;


		// Compare results
		$this->assertEquals($result1, $result2);
		$this->assertEquals($result2, $result3);
		$this->assertEquals($result3, $result4);
		$this->assertEquals($result4, $result5);
	}

	/**
	 * Test call with no date or range set
	 *
	 * @depends testDefaultCall
	 */
	public function testNoPeriodOrDate()
	{
		$this->_piwik->setRange(null, null);
		$this->_piwik->setDate(null);
		$result = $this->_piwik->getVisitsSummary();

		$this->assertFalse($result);
		$this->assertEquals(1, count($this->_piwik->getErrors()));
	}

	/**
	 * Test that multiple errors were added.
	 */
	public function testMultipleErrors()
	{
		// Test with no access => error 1
		$this->_piwik->setToken('403');
		$result = $this->_piwik->getVisitsSummary();

		$this->assertFalse($result);
		$this->assertEquals(1, count($this->_piwik->getErrors()));

		// Test with wrong url => error 2
		$this->_piwik->setSite('http://example.com/404');
		$result = $this->_piwik->getVisitsSummary();

		$this->assertFalse($result);
		$this->assertEquals(2, count($this->_piwik->getErrors()));
	}

	/**
	 * Test if optional parameters work.
	 */
	public function testOptionalParameters()
	{
		$this->_piwik->setDate('2011-01-11');
		$this->_piwik->setPeriod(Piwik::PERIOD_WEEK);
		$result = $this->_piwik->getWebsites('', [
			'flat' => 1,
		]);

		$this->assertInternalType('array', $result);
		$this->assertEquals('', implode(',', $this->_piwik->getErrors()));
		$this->assertEquals(388, $result[0]->nb_visits);
	}
}
