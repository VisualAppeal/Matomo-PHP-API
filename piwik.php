<?php

/*
Author: VisualAppeal
Website: http://www.visualappeal.de
E-Mail: tim@visualappeal.de

https://github.com/VisualAppeal/Piwik-PHP-API
http://piwik.org/docs/analytics-api/reference/

Version: 0.6
*/
class Piwik {

	const PERIOD_DAY = 'day';
	const PERIOD_WEEK = 'week';
	const PERIOD_MONTH = 'month';
	const PERIOD_YEAR = 'year';
	const PERIOD_RANGE = 'range';
	
	const DATE_TODAY = 'today';
	const DATE_YESTERDAY = 'yesterday';
	
	const FORMAT_XML = 'xml';
	const FORMAT_JSON = 'json';
	const FORMAT_CSV = 'csv';
	const FORMAT_TSV = 'tsv';
	const FORMAT_HTML = 'html';
	const FORMAT_RSS = 'rss';
	const FORMAT_PHP = 'php';
	
	private $_site = '';
	private $_token = '';
	private $_siteId = 0;
	private $_format = self::FORMAT_PHP;
	private $_language = 'de';
	
	private $_period = self::PERIOD_DAY;
	private $_date = '';
	private $_rangeStart = 'yesterday';
	private $_rangeEnd = null;
	
	private $_errors = array();
	
	function __construct($site, $token, $siteId, $format, $period = self::PERIOD_DAY, $date = self::DATE_YESTERDAY, $rangeStart = self::DATE_YESTERDAY, $rangeEnd = self::DATE_TODAY) {
		$this->_site = $site;
		$this->_token = $token;
		$this->_siteId = $siteId;
		$this->_format = $format;
		$this->_period = self::PERIOD_DAY;
		$this->_rangeStart = $rangeStart;
		$this->_rangeEnd = $rangeEnd;

		$this->_date = $rangeStart;
	}
	
	/* Options */
	
	/*
	 * Set response format
	 *
	 * @param string $format
	 *		FORMAT_XML
	 *		FORMAT_JSON
	 *		FORMAT_CSV
	 *		FORMAT_HTML
	 *		FORMAT_PHP
	 */
	public function setFormat($format) {
		$this->_format = $format;
	}
	
	/*
	 * Set date
	 *
	 * @param string $date
	 *		DATE_TODAY
	 *		DATE_YESTERDAY
	 */
	public function setDate($date) {
		$this->_date = $date;
	}
	
	/*
	 * Set time period
	 *
	 * @param string $period
	 *		PERIOD_DAY
	 *		PERIOD_MONTH
	 *		PERIOD_WEEK
	 *		PERIOD_YEAR
	 *		PERIOD_RANGE
	 */
	public function setPeriod($period) {
		$this->_period = $period;
	}
	
	/*
	 * Set date range
	 *
	 * @param string $rangeStart e.g. 2012-02-10 (YYYY-mm-dd) or last5(lastX), previous12(previousY)...
	 * @param string $rangeEnd e.g. 2012-02-12. Leave this parameter empty to request all data from $rangeStart until now
	 */
	public function setRange($rangeStart, $rangeEnd = null) {
		$this->_date = '';
		$this->_rangeStart = $rangeStart;
		$this->_rangeEnd = $rangeEnd;
		
		if (is_null($rangeEnd)) {
			$this->_date = $rangeStart;
		}
	}
	
	/*
	 * Reset all default variables
	 */
	public function reset() {
		$this->_period = self::PERIOD_DAY;
		$this->_date = '';
		$this->_rangeStart = 'yesterday';
		$this->_rangeEnd = null;
		
		$this->_errors = array();
	}
	
	/* 
	 * Make API request
	 *
	 * @param string $method
	 */
	private function _request($method, $params = array()) {
		$url = $this->_parseUrl($method, $params);
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		$buffer = curl_exec($handle);
		curl_close($handle);
		
		if (!empty($buffer))
			$request = $this->_parseRequest($buffer);
		else
			$request = false;
		
		return $this->_finishRequest($request, $method);
	}
	
	/*
	 * Validate request and return the values
	 *
	 * @param obj $request
	 * @param string $method
	 */
	private function _finishRequest($request, $method) {
		$valid = $this->_validRequest($request);
		
		if ($valid === true) {
			if (isset($request->value))
				return $request->value;
			else {
				return $request;
			}
		}
		else {
			$request = $this->_addError($valid.' ('.$this->_parseUrl($method).')');
			return false;
		}
	}
	
	/*
	 * Create request url with parameters
	 *
	 * @param string $method The request method
	 * @param array $params Request params
	 */
	private function _parseUrl($method, array $params = array()) {
		$params = array(
			'module' => 'API',
			'method' => $method,
			'token_auth' => $this->_token,
			'idSite' => $this->_siteId,
			'period' => $this->_period,
			'format' => $this->_format,
		) + $params;
		
		if ($this->_period != self::PERIOD_RANGE) {
			$params = $params + array(
				'date' => $this->_date,
			);
		}
		else {
			$params = $params + array(
				'date' => $this->_rangeStart.','.$this->_rangeEnd,
			);
		}
		
		$url = $this->_site;
		
		$i = 0;
		foreach ($params as $param => $val) {
			if (!empty($val)) {
				$i++;
				if ($i > 1)
					$url .= '&';
				else
					$url .= '?';
				
				if (is_array($val))
					$val = implode(',', $val);
				$url .= $param . '=' . $val;
			}
		}
		
		return $url;
	}
	
	/*
	 * Validate the request result
	 *
	 * @param obj $request
	 */
	private function _validRequest($request) {
		if ($request !== false) {
			if (!isset($request->result) or ($request->result != 'error')) {
				return true;
			}
			return $request->message;
		}
		
		return 'The Request was invalid';
	}
	
	/*
	 * Parse request result
	 *
	 * @param obj $request
	 */
	private function _parseRequest($request) {
		switch ($this->_format) {
			case self::FORMAT_JSON:
				return json_decode($request);
				break;
			default:
				return $request;
		}
	}
	
	/* 
	 * Add error
	 *
	 * @param string $msg Error message
	 */
	private function _addError($msg = '') {
		$this->_errors = $this->_errors + array($msg);
	}
	
	/*
	 * Check for errors
	 */
	public function hasError() {
		return (count($this->_errors));
	}
	
	/*
	 * Return all errors
	 */
	public function getErrors() {
		return $this->_errors;
	}
	
	/* 
	 * MODULE: API 
	 * Get metadata
	 */
	
	/*
	 * Get default metric translations
	 */
	public function getApiDefaultMetricTranslations() {
		$this->reset();
		return $this->_request('API.getDefaultMetricTranslations');
	}
	
	/*
	 * Get default metrics
	 */
	public function getApiDefaultMetrics() {
		$this->reset();
		return $this->_request('API.getDefaultMetrics');
	}
	
	/*
	 * Get default processed metrics
	 */
	public function getApiDefaultProcessedMetrics() {
		$this->reset();
		return $this->_request('API.getDefaultProcessedMetrics');
	}
	
	/*
	 * Get default metrics documentation
	 */
	public function getApiDefaultMetricsDocumentation() {
		$this->reset();
		return $this->_request('API.getDefaultMetricsDocumentation');
	}
	
	/*
	 * Get default metric translations
	 *
	 * @param array $sites Array with the ID's of the sites
	 */
	public function getApiSegmentsMetadata($sites = array()) {
		$this->reset();
		return $this->_request('API.getSegmentsMetadata', array('idSites' => $sites));
	}
	
	/*
	 * Get visit ecommerce status from a site
	 *
	 * @param int $id Site ID
	 */
	public function getApiVisitEcommerceStatusFromId($id) {
		$this->reset();
		return $this->_request('API.getVisitEcommerceStatusFromId', array('id' => $id));
	}
	
	/*
	 * Get visit ecommerce status
	 *
	 * @param string $status
	 */
	public function getApiVisitEcommerceStatus($status) {
		$this->reset();
		return $this->_request('API.getVisitEcommerceStatus', array('status' => $status));
	}
	
	/*
	 * Get the url of the logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getApiLogoUrl($pathOnly = false) {
		$this->reset();
		return $this->_request('API.getLogoUrl', array('pathOnly' => $pathOnly));
	}
	
	/*
	 * Get the url of the header logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getApiHeaderLogoUrl($pathOnly) {
		$this->reset();
		return $this->_request('API.getHeaderLogoUrl', array('pathOnly' => $pathOnly));
	}
	
	/*
	 * Get metadata from the API
	 *
	 * @param int $idSite Site ID
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param array $apiParameters Parameters
	 * @param string $language
	 * @param string $period
	 * @param string $date
	 */
	public function getApiMetadata($idSite, $apiModule, $apiAction, $apiParameters = array(), $language = '', $period = '', $date = '') {
		$this->reset();
		return $this->_request('API.getMetadata', array(
			'idSite' => $idSite,
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'apiParameters' => $apiParameters,
			'language' => $language,
			'period' => $period,
			'date' => $date,
		));
	}
	
	/*
	 * Get metadata from a report
	 *
	 * @param array $idSites Array with the ID's of the sites
	 * @param string $period
	 * @param string $date
	 */
	public function getApiReportMetadata($idSites = array(), $period = '', $date = '') {
		$this->reset();
		return $this->_request('API.getReportMetadata', array(
			'idSites' => $idSites,
			'period' => $period,
			'date' => $date,
		));
	}
	
	/*
	 * Get processed report
	 *
	 * @param int $idSite ID of the sites
	 * @param string $period
	 * @param string $date
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param string $segment
	 * @param array $ApiParameters
	 * @param int $idGoal
	 * @param string $language
	 * @param boolean $showTimer
	 */
	public function getApiProcessedReport($idSite, $period, $date, $apiModule, $apiAction, $segment = '', $apiParameters = array(), $idGoal = '', $language = '', $showTimer = '1') {
		$this->reset();
		return $this->_request('API.getProcessedReport', array(
			'idSite' => $idSite,
			'period' => $period,
			'date' => $date,
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'segment' => $segment,
			'apiParameters' => $apiParameters,
			'idGoal' => $idGoal,
			'language' => $language,
			'showTimer' => $showTimer,
		));
	}
	
	/*
	 * Unknown
	 *
	 * @param int $idSite ID of the site
	 * @param string $period
	 * @param string $date
	 * @param string $segment
	 * @param string $columns
	 */
	public function getApi($idSite, $period, $date, $segment = '', $columns = '') {
		$this->reset();
		return $this->_request('API.get', array(
			'idSite' => $idSite,
			'period' => $period,
			'date' => $date,
			'segment' => $segment,
			'columns' => $columns,
		));
	}
	
	/* VisitsSummary */
	 
	/*
	 * Get visitor count
	 */
	public function getVisits() {
		return $this->_request('VisitsSummary.getVisits');
	}
	
	/*
	 * Get unique visitor count
	 */
	public function getVisitsUnique() {
		return $this->_request('VisitsSummary.getUniqueVisitors');
	}
	
	/*
	 * Get the visit lengths
	 */
	public function getVisitsLength() {
		return $this->_request('VisitsSummary.getSumVisitsLength');
	}
	
	/* UserSettings */
	
	/*
	 * Get user resolution
	 */
	public function getUserResolution() {
		return $this->_request($this->_parseUrl('UserSettings.getResolution'));
	}
	
	/*
	 * Get user configuration
	 */
	public function getUserConfiguration() {
		return $this->_request($this->_parseUrl('UserSettings.getConfiguration'));
	}
	
	/*
	 * Get user operatiing system
	 */
	public function getUserOS() {
		return $this->_request($this->_parseUrl('UserSettings.getOS'));
	}
	
	/*
	 * Get user browser
	 */
	public function getUserBrowser() {
		return $this->_request($this->_parseUrl('UserSettings.getBrowser'));
	}
	
	/*
	 * Get user browser type
	 */
	public function getUserBrowserType() {
		return $this->_request($this->_parseUrl('UserSettings.getBrowserType'));
	}
	
	/*
	 * Get user plugins
	 */
	public function getUserPlugin() {
		return $this->_request($this->_parseUrl('UserSettings.getPlugin'));
	}
}


?>