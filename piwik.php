<?php
/*
Author: VisualAppeal
Website: http://www.visualappeal.de
E-Mail: hallo@visualappeal.de

Class: Piwik API
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
	const FORMAT_HTML = 'html';
	const FORMAT_PHP = 'php';
	
	private $_site = '';
	private $_token = '';
	private $_siteId = 0;
	private $_format = Piwik::FORMAT_PHP;
	private $_language = 'de';
	
	private $_period = Piwik::PERIOD_DAY;
	private $_date = '';
	private $_rangeStart = 'yesterday';
	private $_rangeEnd = '';
	
	private $_handle = NULL;
	
	private $_errors = array();
	
	function __construct($site, $token, $siteId, $format, $period = Piwik::PERIOD_DAY, $date = Piwik::DATE_YESTERDAY, $rangeStart = Piwik::DATE_YESTERDAY, $rangeEnd = Piwik::DATE_TODAY) {
		$this->_site = $site;
		$this->_token = $token;
		$this->_siteId = $siteId;
		$this->_format = $format;
		$this->_period = Piwik::PERIOD_DAY;
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
	
	public function setDate($date) {
		$this->_date = $date;
	}
	
	public function setPeriod($period) {
		$this->_period = $period;
	}
	
	public function setRange($rangeStart, $rangeEnd) {
		$this->_rangeStart = $rangeStart;
		$this->_rangeEnd = $rangeEnd;
		
		if ($rangeStart == $rangeEnd) {
			$this->_date = $rangeStart;
		}
	}
	
	/* Requests */
	private function _request($url) {
		$this->_handle = curl_init();
		curl_setopt($this->_handle, CURLOPT_URL, $url);
		curl_setopt($this->_handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->_handle, CURLOPT_RETURNTRANSFER, 1);
		$buffer = curl_exec($this->_handle);
		curl_close($this->_handle);
		
		if (!empty($buffer))
			return $this->_parseRequest($buffer);
		else
			return false;
	}
	
	private function _parseUrl($method, array $params = array()) {
		$params = $params + array(
			'module' => 'API',
			'method' => $method,
			'token_auth' => $this->_token,
			'idSite' => $this->_siteId,
			'period' => $this->_period,
			'format' => $this->_format,
		);
		
		if ($this->_period != Piwik::PERIOD_RANGE)
			$params = $params + array(
				'date' => $this->_date,
			);
		else
			$params = $params + array(
				'date' => $this->_rangeStart.','.$this->_rangeEnd,
			);
		
		$url = $this->_site;
		
		$i = 0;
		foreach ($params as $param => $val) {
			$i++;
			if ($i > 1)
				$url .= '&';
			else
				$url .= '?';
			$url .= $param . '=' . $val;
		}
		
		return $url;
	}
	
	private function _validRequest($request) {
		if ($request !== false) {
			if (!isset($request->result) or ($request->result != 'error')) {
				return true;
			}
			return $request->message;
		}
		
		return 'The Request was invalid';
	}
	
	private function _parseRequest($request) {
		switch ($this->_format) {
			case Piwik::FORMAT_JSON:
				return json_decode($request);
				break;
			default:
				return $request;
		}
	}
	
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
	
	/* Error handling */
	private function _addError($msg = '') {
		$this->_errors = $this->_errors + array($msg);
	}
	
	public function hasError() {
		return (count($this->_errors) > 0);
	}
	
	public function getErrors() {
		return $this->_errors;
	}
	
	/* VisitsSummary */
	public function getVisits() {
		$method = 'VisitsSummary.getVisits';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);
	}
	
	public function getVisitsUnique() {
		$method = 'VisitsSummary.getUniqueVisitors';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);
	}
	
	public function getVisitsLength() {
		$method = 'VisitsSummary.getSumVisitsLength';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);
	}
	
	/* UserSettings */
	public function getUserResolution() {
		$method = 'UserSettings.getResolution';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);
	}
	
	public function getUserConfiguration() {
		$method = 'UserSettings.getConfiguration';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);		
	}
	
	public function getUserOS() {
		$method = 'UserSettings.getOS';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);		
	}
	
	public function getUserBrowser() {
		$method = 'UserSettings.getBrowser';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);		
	}
	
	public function getUserBrowserType() {
		$method = 'UserSettings.getBrowserType';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);		
	}
	
	public function getUserPlugin() {
		$method = 'UserSettings.getPlugin';
		$request = $this->_request($this->_parseUrl($method));
		
		return $this->_finishRequest($request, $method);		
	}
}


?>