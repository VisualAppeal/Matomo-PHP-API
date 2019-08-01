<?php

use PHPUnit\Framework\TestCase;

use VisualAppeal\InvalidRequestException;
use VisualAppeal\Matomo;

class MatomoTest extends TestCase
{
	const TEST_SITE_URL = 'https://demo.matomo.org/';

	const TEST_SITE_ID = 62;

	const TEST_TOKEN = 'anonymous';

	/**
	 * Matomo api instance.
	 *
	 * @var Matomo
	 */
	private $_matomo = null;

    /**
     * Set up test class.
     */
	protected function setUp(): void
	{
		$this->_matomo = new Matomo(self::TEST_SITE_URL, self::TEST_TOKEN, self::TEST_SITE_ID);
	}

    /**
     * Cleanup test class.
     */
	protected function tearDown(): void
	{
		unset($this->_matomo);
		$this->_matomo = null;
	}

	/**
	 * Test creation of class instance.
	 */
	public function testInit()
	{
		$this->assertInstanceOf(Matomo::class, $this->_matomo);
	}

    /**
     * Test the default api call.
     *
     * @throws InvalidRequestException
     */
	public function testDefaultCall()
	{
		$result = $this->_matomo->getVisits();

		$this->assertIsInt($result);
	}

    /**
     * Test the result of a time range.
     *
     * @depends testDefaultCall
     * @throws InvalidRequestException
     */
	public function testRangePeriod()
	{
		$this->_matomo->setPeriod(Matomo::PERIOD_RANGE);
		$this->_matomo->setRange(date('Y-m-d', time() - 3600 * 24), date('Y-m-d'));
		$result = $this->_matomo->getVisitsSummary();

		$this->assertIsObject($result);
	}

    /**
     * Test the result of one day.
     *
     * @depends testDefaultCall
     * @throws InvalidRequestException
     */
	public function testDayPeriod()
	{
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setDate(date('Y-m-d', time() - 3600 * 24));
		$result = $this->_matomo->getVisitsSummary();

		$this->assertIsObject($result);
	}

    /**
     * Test the result of multiple dates.
     *
     * @depends testDayPeriod
     * @link https://github.com/VisualAppeal/Matomo-PHP-API/issues/14
     * @throws InvalidRequestException
     */
	public function testMultipleDates()
	{
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setRange(date('Y-m-d', time() - 3600 * 24 * 6), date('Y-m-d'));
		$result = $this->_matomo->getVisitsSummary();

		$this->assertIsObject($result);
		$this->assertEquals(7, count((array) $result));
	}

    /**
     * Test if all dates and ranges get the same result
     *
     * @depends testRangePeriod
     * @depends testMultipleDates
     * @throws InvalidRequestException
     */
	public function testDateEquals()
	{
		$date = date('Y-m-d', time() - 3600 * 24 * 7);

		// Range
		$this->_matomo->setPeriod(Matomo::PERIOD_RANGE);
		$this->_matomo->setRange($date, $date);

		$result1 = $this->_matomo->getVisits();
		$this->_matomo->reset();

		// Single date
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setDate($date);

		$result2 = $this->_matomo->getVisits();
		$this->_matomo->reset();

		// Multiple dates
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setRange($date, $date);

		$result3 = $this->_matomo->getVisits();
		$result3 = $result3->$date;
		$this->_matomo->reset();

		// Multiple dates with default range end
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setRange($date);

		$result4 = $this->_matomo->getVisits();
		$result4 = $result4->$date;
		$this->_matomo->reset();

		// previousX respectively lastX date
		$this->_matomo->setPeriod(Matomo::PERIOD_DAY);
		$this->_matomo->setRange('previous7');

		$result5 = $this->_matomo->getVisits();
		$result5 = $result5->$date;


		// Compare results
		$this->assertEquals($result1, $result2);
		$this->assertEquals($result2, $result3);
		$this->assertEquals($result3, $result4);
		$this->assertEquals($result4, $result5);
	}

    /**
     * Test call with no date or range set.
     *
     * @depends testDefaultCall
     * @throws InvalidRequestException
     */
	public function testNoPeriodOrDate()
	{
		$this->_matomo->setRange(null, null);
		$this->_matomo->setDate(null);

		$this->expectException(InvalidArgumentException::class);
		$this->_matomo->getVisitsSummary();
	}

    /**
     * Test that an exception is thrown with an invalid access token.
     *
     * @throws InvalidRequestException
     */
	public function testInvalidAccessToken()
    {
        $this->_matomo->setToken('403');

        $this->expectException(InvalidRequestException::class);
        $this->_matomo->getVisitsSummary();
    }

    /**
     * Test that an exception is thrown with an invalid url.
     *
     * @throws InvalidRequestException
     */
    public function testInvalidUrl()
    {
		$this->_matomo->setSite('http://example.com/404');

		$this->expectException(InvalidRequestException::class);
		$this->_matomo->getVisitsSummary();
	}

    /**
     * Test if optional parameters work.
     *
     * @throws InvalidRequestException
     */
	public function testOptionalParameters()
	{
		$this->_matomo->setDate('2019-07-01');
		$this->_matomo->setPeriod(Matomo::PERIOD_WEEK);
		$result = $this->_matomo->getWebsites('', [
			'flat' => 1,
		]);

		$this->assertIsArray($result);
		$this->assertEquals(1198, $result[0]->nb_visits);
	}

    /**
     * Test if the response contains custom variables.
     *
     * @throws InvalidRequestException
     */
	public function testCustomVariables()
	{
		$this->_matomo->setDate('2019-07-01');
		$this->_matomo->setPeriod(Matomo::PERIOD_WEEK);
		$result = $this->_matomo->getCustomVariables();

		$this->assertEquals(15, count($result));
	}

	/**
	 * Test if matamo can be used without the site ID parameter.
	 *
	 * @throws InvalidRequestException
	 */
	public function testEmptySiteId()
	{
		$matomo = new Matomo(self::TEST_SITE_URL, self::TEST_TOKEN);
		$this->assertNull($matomo->getSiteId());

		$matomo->setSiteId(null);
		$this->assertNull($matomo->getSiteId());

		$this->assertIsObject($matomo->getTimezonesList());
	}
}
