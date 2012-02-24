<?php

/*

Author: VisualAppeal
Website: http://www.visualappeal.de
E-Mail: tim@visualappeal.de

https://github.com/VisualAppeal/Piwik-PHP-API
http://piwik.org/docs/analytics-api/reference/

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
	
	private $_limit = '';
	
	private $_errors = array();
	
	/*
	 * Create new instance
	 *
	 * @param string $site URL of the piwik installation
	 * @param string $token API Access token
	 * @param int $siteId ID of the site
	 * @param string $format
	 * @param string $period
	 * @param string $date
	 * @param string $rangeStart
	 * @param string $rangeEnd
	 */
	function __construct($site, $token, $siteId, $format = self::FORMAT_JSON, $period = self::PERIOD_DAY, $date = self::DATE_YESTERDAY, $rangeStart = '', $rangeEnd = null) {
		$this->_site = $site;
		$this->_token = $token;
		$this->_siteId = $siteId;
		$this->_format = $format;
		$this->_period = self::PERIOD_DAY;
		$this->_rangeStart = $rangeStart;
		$this->_rangeEnd = $rangeEnd;

		$this->_date = $rangeStart;
	}
	
	/* 
	 * Getter & Setter
	 */
	
	/*
	 * Get response format
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->_format;
	}
	
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
	 * Get date
	 *
	 * @return string
	 */
	public function getDate() {
		return $this->_date;
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
	 * Get time period
	 *
	 * @return string
	 */
	public function getPeriod() {
		return $this->_period;
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
	 * Get the date range comma seperated
	 *
	 * @return string
	 */
	public function getRange() {
		if (empty($this->_rangeEnd))
			return $this->_rangeStart;
		else
			return $this->_rangeStart.','.$this->_rangeEnd;
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
	 * Get the limit of returned rows
	 *
	 * @return int
	 */
	public function getLimit() {
		return intval($this->_limit);
	}
	
	/*
	 * Set the limit of returned rows
	 *
	 * @param int $limit
	 */
	public function setLimit($limit) {
		$this->_limit = $limit;
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
		
		return urlencode($url);
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
	 * API metadata
	 */
	
	/*
	 * Get default metric translations
	 */
	public function getDefaultMetricTranslations() {
		return $this->_request('API.getDefaultMetricTranslations');
	}
	
	/*
	 * Get default metrics
	 */
	public function getDefaultMetrics() {
		return $this->_request('API.getDefaultMetrics');
	}
	
	/*
	 * Get default processed metrics
	 */
	public function getDefaultProcessedMetrics() {
		return $this->_request('API.getDefaultProcessedMetrics');
	}
	
	/*
	 * Get default metrics documentation
	 */
	public function getDefaultMetricsDocumentation() {
		return $this->_request('API.getDefaultMetricsDocumentation');
	}
	
	/*
	 * Get default metric translations
	 *
	 * @param array $sites Array with the ID's of the sites
	 */
	public function getSegmentsMetadata($sites = array()) {
		return $this->_request('API.getSegmentsMetadata', array('idSites' => $sites));
	}
	
	/*
	 * Get visit ecommerce status from a site
	 *
	 * @param int $id Site ID
	 */
	public function getVisitEcommerceStatusFromId($id) {
		return $this->_request('API.getVisitEcommerceStatusFromId', array('id' => $id));
	}
	
	/*
	 * Get visit ecommerce status
	 *
	 * @param string $status
	 */
	public function getVisitEcommerceStatus($status) {
		return $this->_request('API.getVisitEcommerceStatus', array('status' => $status));
	}
	
	/*
	 * Get the url of the logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getLogoUrl($pathOnly = false) {
		return $this->_request('API.getLogoUrl', array('pathOnly' => $pathOnly));
	}
	
	/*
	 * Get the url of the header logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getHeaderLogoUrl($pathOnly) {
		return $this->_request('API.getHeaderLogoUrl', array('pathOnly' => $pathOnly));
	}
	
	/*
	 * Get metadata from the API
	 *
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param array $apiParameters Parameters
	 */
	public function getMetadata($apiModule, $apiAction, $apiParameters = array()) {
		return $this->_request('API.getMetadata', array(
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'apiParameters' => $apiParameters,
		));
	}
	
	/*
	 * Get metadata from a report
	 *
	 * @param array $idSites Array with the ID's of the sites
	 */
	public function getReportMetadata($idSites = array()) {
		return $this->_request('API.getReportMetadata', array(
			'idSites' => $idSites,
		));
	}
	
	/*
	 * Get processed report
	 *
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param string $segment
	 * @param array $ApiParameters
	 * @param int $idGoal
	 * @param boolean $showTimer
	 */
	public function getProcessedReport($apiModule, $apiAction, $segment = '', $apiParameters = array(), $idGoal = '', $showTimer = '1') {
		return $this->_request('API.getProcessedReport', array(
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'segment' => $segment,
			'apiParameters' => $apiParameters,
			'idGoal' => $idGoal,
			'showTimer' => $showTimer,
		));
	}
	
	/*
	 * Unknown
	 *
	 * @param string $segment
	 * @param string $columns
	 */
	public function getApi($segment = '', $columns = '') {
		return $this->_request('API.get', array(
			'segment' => $segment,
			'columns' => $columns,
		));
	}
	
	/*
	 * MODULE: ACTIONS
	 * Reports for visitor actions
	 */
	
	/*
	 * Get actions
	 *
	 * @param string $segment
	 * @param string $columns
	 */
	public function getAction($segment = '', $columns = '') {
		return $this->_request('Actions.get', array(
			'segment' => $segment,
			'columns' => $columns,
		));
	}
	
	/*
	 * Get page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageUrls', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get entry page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getEntryPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getEntryPageUrls', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get exit page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getExitPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getExitPageUrls', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get page url information
	 *
	 * @param string $pageUrl The page url
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageUrl($pageUrl, $segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageUrl', array(
			'pageUrl' => $pageUrl,
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get page titles
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageTitles($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageTitles', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get page titles
	 *
	 * @param string $pageName The page name
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageTitle($pageName, $segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageTitle', array(
			'pageName' => $pageName,
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get downloads
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDownloads($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getDownloads', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get download information
	 *
	 * @param string $downloadUrl URL of the download
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDownload($downloadUrl, $segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getDownload', array(
			'downloadUrl' => $downloadUrl,
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get outlinks
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getOutlinks($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getOutlinks', array(
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * Get outlink information
	 *
	 * @param string $outlinkUrl URL of the outlink
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getOutlink($outlinkUrl, $segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getDownload', array(
			'outlinkUrl' => $outlinkUrl,
			'segment' => $segment,
			'columns' => $columns,
			'idSubtable' => $idSubtable,
		));
	}
	
	/*
	 * MODULE: CUSTOM VATIABLES
	 * Custom variable information
	 */
	
	/*
	 * Get custom variables
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getCustomVariables($segment = '', $expanded = '') {
		return $this->_request('CustomVariables.getCustomVariables', array(
			'segment' => $segment,
			'columns' => $columns,
		));
	}
	
	/*
	 * Get information about a custom variable
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getCustomVariable($idSubtable, $segment = '', $expanded = '') {
		return $this->_request('CustomVariables.getCustomVariablesValuesFromNameId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
			'columns' => $columns,
		));
	}
	
	/*
	 * MODULE: EXAMPLE API
	 * Get api and piwiki information
	 */
	
	/*
	 * Get the piwik version
	 */
	public function getPiwikiVersion() {
		return $this->_request('ExampleAPI.getPiwikVersion');
	}
	
	/*
	 * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
	 */
	public function getAnswerToLife() {
		return $this->_request('ExampleAPI.getAnswerToLife');
	}
	
	/*
	 * 
	 */
	public function getObject() {
		return $this->_request('ExampleAPI.getObject');
	}
	
	/*
	 * Get the sum of the parameters
	 *
	 * @param int $a
	 * @param int $b
	 */
	public function getSum($a, $b) {
		return $this->_request('ExampleAPI.getSum');
	}
	
	/*
	 * Returns nothing but the success of the request
	 */
	public function getNull() {
		return $this->_request('ExampleAPI.getNull');
	}
	
	/*
	 * Get a short piwik description
	 */
	public function getDescriptionArray() {
		return $this->_request('ExampleAPI.getDescriptionArray');
	}
	
	/*
	 * Get a short comparison with other analytic software
	 */
	public function getCompetitionDatatable() {
		return $this->_request('ExampleAPI.getCompetitionDatatable');
	}
	
	/*
	 * Get information about 42
	 * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
	 */
	public function getMoreInformationAnswerToLife() {
		return $this->_request('ExampleAPI.getMoreInformationAnswerToLife');
	}
	
	/*
	 * Get a multidimensional array
	 */
	public function getMultiArray() {
		return $this->_request('ExampleAPI.getMultiArray');
	}
	
	/*
	 * MODULE: GOALS
	 * Handle goals
	 */
	
	/*
	 * Get all goals
	 */
	public function getGoals() {
		return $this->_request('Goals.getGoals');
	}
	
	/*
	 * Add a goal
	 *
	 * @param string $name
	 * @param string $matchAttribute
	 * @param string $pattern
	 * @param string $patternType
	 * @param boolean $caseSensitive
	 * @param float $revenue
	 * @param boolean $allowMultipleConversionsPerVisit
	 */
	public function addGoal($name, $matchAttribute, $pattern, $patternType, $caseSensitive = '', $revenue = '', $allowMultipleConversionsPerVisit = '') {
		return $this->_request('Goals.addGoal', array(
			'name' => $name,
			'matchAttribute' => $matchAttribute,
			'pattern' => $pattern,
			'patternType' => $patternType,
			'caseSensitive' => $caseSensitive,
			'revenue' => $revenue,
			'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
		));
	}
	
	/*
	 * Update a goal
	 *
	 * @param int $idGoal
	 * @param string $name
	 * @param string $matchAttribute
	 * @param string $pattern
	 * @param string $patternType
	 * @param boolean $caseSensitive
	 * @param float $revenue
	 * @param boolean $allowMultipleConversionsPerVisit
	 */
	public function updateGoal($idGoal, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = '', $revenue = '', $allowMultipleConversionsPerVisit = '') {
		return $this->_request('Goals.updateGoal', array(
			'idGoal' => $idGoal,
			'name' => $name,
			'matchAttribute' => $matchAttribute,
			'pattern' => $pattern,
			'patternType' => $patternType,
			'caseSensitive' => $caseSensitive,
			'revenue' => $revenue,
			'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
		));
	}
	
	/*
	 * Delete a goal
	 *
	 * @param int $idGoal
	 */
	public function deleteGoal($idGoal) {
		return $this->_request('Goals.deleteGoal', array(
			'idGoal' => $idGoal,
		));
	}
	
	/*
	 * Get the SKU of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsSku($abandonedCarts) {
		return $this->_request('Goals.getItemsSku', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}
	
	/*
	 * Get the name of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsName($abandonedCarts) {
		return $this->_request('Goals.getItemsName', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}
	
	/*
	 * Get the categories of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsCategory($abandonedCarts) {
		return $this->_request('Goals.getItemsCategory', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}
	
	/*
	 * Get conversion rates from a goal
	 *
	 * @param string $segment
	 * @param int $idGoal
	 * @param array $columns
	 */
	public function getGoal($segment = '', $idGoal = '', $columns = array()) {
		return $this->_request('Goals.get', array(
			'segment' => $segment,
			'idGoal' => $idGoal,
			'columns' => $columns,
		));
	}
	
	/*
	 * Get information about a time period and it's conversion rates
	 *
	 * @param string $segment
	 * @param int $idGoal
	 */
	public function getDaysToConversion($segment = '', $idGoal = '') {
		return $this->_request('Goals.getDaysToConversion', array(
			'segment' => $segment,
			'idGoal' => $idGoal,
		));
	}
	
	/*
	 * Get information about how many site visits create a conversion
	 *
	 * @param string $segment
	 * @param int $idGoal
	 */
	public function getVisitsUntilConversion($segment = '', $idGoal = '') {
		return $this->_request('Goals.getVisitsUntilConversion', array(
			'segment' => $segment,
			'idGoal' => $idGoal,
		));
	}
	
	/* 
	 * MODULE: IMAGE GRAPH
	 * Generate png graphs
	 */
	
	const GRAPH_EVOLUTION = 'evolution';
	const GRAPH_VERTICAL_BAR = 'verticalBar';
	const GRAPH_PIE = 'pie';
	const GRAPH_PIE_3D = '3dPie';
	
	/*
	 * Generate a png report
	 *
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param string
	 *		GRAPH_EVOLUTION
	 *		GRAPH_VERTICAL_BAR
	 *		GRAPH_PIE
	 *		GRAPH_PIE_3D
	 * @param int $outputType
	 * @param string $column
	 * @param boolean $showMetricTitle
	 * @param int $width
	 * @param int $height
	 * @param int $fontSize
	 * @param boolean $aliasedGraph "by default, Graphs are "smooth" (anti-aliased). If you are generating hundreds of graphs and are concerned with performance, you can set aliasedGraph=0. This will disable anti aliasing and graphs will be generated faster, but look less pretty."
	 * @param array $colors Use own colors instead of the default. The colors has to be in hexadecimal value without '#'
	 */
	public function getImageGraph($apiModule, $apiAction, $graphType = '', $outputType = '0', $column = '', $showMetricTitle = '1', $width = '', $height = '', $fontSize = '9', $aliasedGraph = '1', $colors = array()) {
		return $this->_request('ImageGraph.get', array(
			'segment' => $apiModule,
			'apiAction' => $apiAction,
			'graphType' => $graphType,
			'outputType' => $outputType,
			'column' => $column,
			'showMetricTitle' => $showMetricTitle,
			'width' => $width,
			'height' => $height,
			'fontSize' => $fontSize,
			'aliasedGraph' => $aliasedGraph,
			'colors' => $colors,
		));
	}
	
	/* 
	 * MODULE: LANGUAGES MANAGER
	 * Manage languages
	 */
	
	/*
	 * Proofe if language is available
	 *
	 * @param string $languageCode
	 */
	public function getLanguageAvailable($languageCode) {
		return $this->_request('LanguagesManager.isLanguageAvailable', array(
			'languageCode' => $languageCode,
		));
	}
	
	/*
	 * Get all available languages
	 */
	public function getAvailableLanguages() {
		return $this->_request('LanguagesManager.getAvailableLanguages');
	}
	
	/*
	 * Get all available languages with information
	 */
	public function getAvailableLanguagesInfo() {
		return $this->_request('LanguagesManager.getAvailableLanguagesInfo');
	}
	
	/*
	 * Get all available languages with their names
	 */
	public function getAvailableLanguageNames() {
		return $this->_request('LanguagesManager.getAvailableLanguageNames');
	}
	
	/*
	 * Get translations for a language
	 *
	 * @param string $languageCode
	 */
	public function getTranslations($languageCode) {
		return $this->_request('LanguagesManager.getTranslationsForLanguage', array(
			'languageCode' => $languageCode,
		));
	}
	
	/*
	 * Get the language for the user with the login $login
	 *
	 * @param string $login
	 */
	public function getLanguageForUser($login) {
		return $this->_request('LanguagesManager.getLanguageForUser', array(
			'login' => $login,
		));
	}
	
	/*
	 * Set the language for the user with the login $login
	 *
	 * @param string $login
	 * @param string $languageCode
	 */
	public function getLanguageForUser($login, $languageCode) {
		return $this->_request('LanguagesManager.setLanguageForUser', array(
			'login' => $login,
			'languageCode' => $languageCode,
		));
	}
	
	/* 
	 * MODULE: LIVE
	 * Request live data
	 */
	
	/*
	 * Get a short information about the visit counts in the last minutes 
	 *
	 * @param int $lastMinutes Default: 60
	 * @param string $segment
	 */
	public function getCounters($lastMinutes = 60, $segment = '') {
		return $this->_request('Live.getCounters', array(
			'lastMinutes' => $lastMinutes,
			'segment' => $segment,
		));
	}
	
	/*
	 * Get information about the last visits
	 *
	 * @param string $segment
	 * @param int $filterLimit
	 * @param int $maxIdVisit
	 * @param string $minTimestamp
	 */
	public function getLastVisitsDetails($segment = '', $filterLimit = '', $maxIdVisit = '', $minTimestamp = '') {
		return $this->_request('Live.getLastVisitsDetails', array(
			'segment' => $segment,
			'filterLimit' => $filterLimit,
			'maxIdVisit' => $maxIdVisit,
			'minTimestamp' => $minTimestamp,
		));
	}
	
	/* 
	 * MODULE: MULTI SITES
	 * Get information about multiple sites
	 */
	
	/*
	 * Get information about multiple sites
	 *
	 * @param string $segment
	 */
	public function getMultiSites($segment = '') {
		return $this->_request('MultiSites.getAll', array(
			'segment' => $segment,
		));
	}
	
	/* 
	 * MODULE: 
	 * VisitsSummary 
	 */
	 
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