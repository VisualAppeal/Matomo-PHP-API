<?php namespace VisualAppeal;

/**
 * Repository: https://github.com/VisualAppeal/Piwik-PHP-API
 * Official api reference: http://piwik.org/docs/analytics-api/reference/
 */
class Piwik
{
	const ERROR_INVALID = 10;
	const ERROR_EMPTY = 11;

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
	private $_language = 'en';

	private $_period = self::PERIOD_DAY;
	private $_date = '';
	private $_rangeStart = 'yesterday';
	private $_rangeEnd = null;

	private $_limit = '';

	private $_errors = array();

	public $verifySsl = true;

	/**
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
		$this->_period = $period;
		$this->_rangeStart = $rangeStart;
		$this->_rangeEnd = $rangeEnd;

		if (!empty($rangeStart))
			$this->setRange($rangeStart, $rangeEnd);
		else
			$this->setDate($date);
	}

	/**
	 * Getter & Setter
	 */

	/**
	 * Get the url of the piwik installation
	 *
	 * @return string
	 */
	public function getSite() {
		return $this->_site;
	}

	/**
	 * Set the URL of the piwik installation
	 *
	 * @param string $url
	 */
	public function setSite($url) {
		$this->_site = $url;

		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string
	 */
	public function getToken() {
		return $this->_token;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 */
	public function setToken($token) {
		$this->_token = $token;

		return $this;
	}

	/**
	 * Get current site ID
	 *
	 * @return int
	 */
	public function getSiteId() {
		return intval($this->_siteId);
	}

	/**
	 * Set current site ID
	 *
	 * @param int $id
	 */
	public function setSiteId($id) {
		$this->_siteId = $id;

		return $this;
	}

	/**
	 * Get response format
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->_format;
	}

	/**
	 * Set response format
	 *
	 * @param string $format
	 *		FORMAT_XML
	 *		FORMAT_JSON
	 *		FORMAT_CSV
	 *		FORMAT_TSV
	 *		FORMAT_HTML
	 *		FORMAT_RSS
	 *		FORMAT_PHP
	 */
	public function setFormat($format) {
		$this->_format = $format;

		return $this;
	}

	/**
	 * Get language
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->_language;
	}

	/**
	 * Set language
	 *
	 * @param string $language
	 */
	public function setLanguage($language) {
		$this->_language = $language;

		return $this;
	}

	/**
	 * Get date
	 *
	 * @return string
	 */
	public function getDate() {
		return $this->_date;
	}

	/**
	 * Set date
	 *
	 * @param string $date
	 *		DATE_TODAY
	 *		DATE_YESTERDAY
	 */
	public function setDate($date) {
		$this->_date = $date;

		return $this;
	}

	/**
	 * Get  period
	 *
	 * @return string
	 */
	public function getPeriod() {
		return $this->_period;
	}

	/**
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

		return $this;
	}

	/**
	 * Get the date range comma seperated
	 *
	 * @return string
	 */
	public function getRange() {
		if (empty($this->_rangeEnd))
			return $this->_rangeStart;
		else
			return $this->_rangeStart . ',' . $this->_rangeEnd;
	}

	/**
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
			$this->_rangeEnd = self::DATE_TODAY;
		}

		return $this;
	}

	/**
	 * Get the limit of returned rows
	 *
	 * @return int
	 */
	public function getLimit() {
		return intval($this->_limit);
	}

	/**
	 * Set the limit of returned rows
	 *
	 * @param int $limit
	 */
	public function setLimit($limit) {
		$this->_limit = $limit;

		return $this;
	}

	/**
	 * Reset all default variables
	 */
	public function reset() {
		$this->_period = self::PERIOD_DAY;
		$this->_date = '';
		$this->_rangeStart = 'yesterday';
		$this->_rangeEnd = null;

		$this->_errors = array();

		return $this;
	}

	/**
	 * Make API request
	 *
	 * @param string $method
	 */
	private function _request($method, $params = array()) {
		$url = $this->_parseUrl($method, $params);
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
		if (!$this->verifySsl)
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

		$buffer = curl_exec($handle);
		curl_close($handle);

		if (!empty($buffer))
			$request = $this->_parseRequest($buffer);
		else
			$request = false;

		return $this->_finishRequest($request, $method, $params);
	}

	/**
	 * Validate request and return the values
	 *
	 * @param obj $request
	 * @param string $method
	 * @param array $params
	 */
	private function _finishRequest($request, $method, $params) {
		$valid = $this->_validRequest($request);

		if ($valid === true) {
			if (isset($request->value))
				return $request->value;
			else {
				return $request;
			}
		} else {
			$request = $this->_addError($valid . ' (' . $this->_parseUrl($method, $params) . ')');
			return false;
		}
	}

	/**
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
			'language' => $this->_language,
		) + $params;

		foreach ($params as $key => $value) {
			$params[$key] = urlencode($value);
		}

		if ($this->_period != self::PERIOD_RANGE) {
			$params = $params + array(
				'date' => $this->_date,
			);
		} else {
			$params = $params + array(
				'date' => $this->_rangeStart . ',' . $this->_rangeEnd,
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

	/**
	 * Validate the request result
	 *
	 * @param obj $request
	 */
	private function _validRequest($request) {
		if (($request !== false) and (!is_null($request))) {
			if (!isset($request->result) or ($request->result != 'error')) {
				return true;
			}
			return $request->message;
		}

		if (is_null($request)) {
			return self::ERROR_EMPTY;
		} else {
			return self::ERROR_INVALID;
		}
	}

	/**
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

	/**
	 * Add error
	 *
	 * @param string $msg Error message
	 */
	private function _addError($msg = '') {
		$this->_errors = $this->_errors + array($msg);
	}

	/**
	 * Check for errors
	 */
	public function hasError() {
		return (count($this->_errors));
	}

	/**
	 * Return all errors
	 */
	public function getErrors() {
		return $this->_errors;
	}

	/**
	 * MODULE: API
	 * API metadata
	 */

	/**
	 * Get current piwik version
	 */
	public function getPiwikVersion() {
		return $this->_request('API.getPiwikVersion');
	}

	/**
	 * Get current ip address (from the server executing this script)
	 */
	public function getIpFromHeader() {
		return $this->_request('API.getIpFromHeader');
	}

	/**
	 * Get current settings
	 */
	public function getSettings() {
		return $this->_request('API.getSettings');
	}

	/**
	 * Get default metric translations
	 */
	public function getDefaultMetricTranslations() {
		return $this->_request('API.getDefaultMetricTranslations');
	}

	/**
	 * Get default metrics
	 */
	public function getDefaultMetrics() {
		return $this->_request('API.getDefaultMetrics');
	}

	/**
	 * Get default processed metrics
	 */
	public function getDefaultProcessedMetrics() {
		return $this->_request('API.getDefaultProcessedMetrics');
	}

	/**
	 * Get default metrics documentation
	 */
	public function getDefaultMetricsDocumentation() {
		return $this->_request('API.getDefaultMetricsDocumentation');
	}

	/**
	 * Get default metric translations
	 *
	 * @param array $sites Array with the ID's of the sites
	 */
	public function getSegmentsMetadata($sites = array()) {
		return $this->_request('API.getSegmentsMetadata', array(
			'idSites' => $sites));
	}

	/**
	 * Get the url of the logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getLogoUrl($pathOnly = false) {
		return $this->_request('API.getLogoUrl', array(
			'pathOnly' => $pathOnly));
	}

	/**
	 * Get the url of the header logo
	 *
	 * @param boolean $pathOnly Return the url (false) or the absolute path (true)
	 */
	public function getHeaderLogoUrl($pathOnly = false) {
		return $this->_request('API.getHeaderLogoUrl', array(
			'pathOnly' => $pathOnly));
	}

	/**
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

	/**
	 * Get metadata from a report
	 *
	 * @param array $idSites Array with the ID's of the sites
	 * @param string $hideMetricsDoc
	 * @param string $showSubtableReports
	 */
	public function getReportMetadata($idSites = array(), $hideMetricsDoc = '', $showSubtableReports = '') {
		return $this->_request('API.getReportMetadata', array(
			'idSites' => $idSites,
			'hideMetricsDoc' => $hideMetricsDoc,
			'showSubtableReports' => $showSubtableReports,
		));
	}

	/**
	 * Get processed report
	 *
	 * @param string $apiModule Module
	 * @param string $apiAction Action
	 * @param string $segment
	 * @param array $ApiParameters
	 * @param int $idGoal
	 * @param boolean $showTimer
	 * @param string $hideMetricsDoc
	 * @param string $idSubtable
	 */
	public function getProcessedReport($apiModule, $apiAction, $segment = '', $apiParameters = array(), $idGoal = '', $showTimer = '1', $hideMetricsDoc = '', $idSubtable = '') {
		return $this->_request('API.getProcessedReport', array(
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'segment' => $segment,
			'apiParameters' => $apiParameters,
			'idGoal' => $idGoal,
			'showTimer' => $showTimer,
			'hideMetricsDoc' => $hideMetricsDoc,
			'idSubtable' => $idSubtable,
		));
	}

	/**
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

	/**
	 * Unknown
	 *
	 * @param $apiModule
	 * @param $apiAction
	 * @param string $segment
	 * @param $column
	 * @param string $idGoal
	 * @param string $legendAppendMetric
	 * @param string $labelUseAbsoluteUrl
	 * @return mixed
	 */
	public function getRowEvolution($apiModule, $apiAction, $segment = '', $column = '', $idGoal = '', $legendAppendMetric = '1', $labelUseAbsoluteUrl = '1') {
		return $this->_request('API.getRowEvolution', array(
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'segment' => $segment,
			'column' => $column,
			'idGoal' => $idGoal,
			'legendAppendMetric ' => $legendAppendMetric,
			'labelUseAbsoluteUrl  ' => $labelUseAbsoluteUrl,
		));
	}

	/**
	 * Unknown
	 *
	 * @param string $segment
	 * @param string $columns
	 */
	public function getLastDate() {
		return $this->_request('API.getLastDate');
	}

	/**
	 * Get the result of multiple requests bundled together
	 * Take as an argument an array of the API methods to send together
	 * For example, array('API.get', 'Action.get', 'DeviceDetection.getType')
	 *
	 * @param array $methods
	 */
	public function getBulkRequest($methods = array()) {
		$urls = array();

		foreach ($methods as $key => $method){
			$urls['urls['.$key.']'] = urlencode('method='.$method);
		}

		return $this->_request('API.getBulkRequest', $urls);
	}

	/**
	 * Get suggested values for segments
	 *
	 * @param string $segmentName
	 */
	public function getSuggestedValuesForSegment($segmentName) {
		return $this->_request('API.getSuggestedValuesForSegment', array(
			'segmentName' => $segmentName,
		));
	}

	/**
	 * MODULE: ACTIONS
	 * Reports for visitor actions
	 */

	/**
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

	/**
	 * Get page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageUrls', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get page URLs after a site search
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageUrlsFollowingSiteSearch($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageUrlsFollowingSiteSearch', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get page titles after a site search
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageTitlesFollowingSiteSearch($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageTitlesFollowingSiteSearch', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get entry page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getEntryPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getEntryPageUrls', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get exit page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getExitPageUrls($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getExitPageUrls', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get page url information
	 *
	 * @param string $pageUrl The page url
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageUrl($pageUrl, $segment = '') {
		return $this->_request('Actions.getPageUrl', array(
			'pageUrl' => $pageUrl,
			'segment' => $segment,
		));
	}

	/**
	 * Get page titles
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageTitles($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getPageTitles', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get entry page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getEntryPageTitles($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getEntryPageTitles', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get exit page urls
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getExitPageTitles($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getExitPageTitles', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get page titles
	 *
	 * @param string $pageName The page name
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getPageTitle($pageName, $segment = '') {
		return $this->_request('Actions.getPageTitle', array(
			'pageName' => $pageName,
			'segment' => $segment,
		));
	}

	/**
	 * Get downloads
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDownloads($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getDownloads', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get download information
	 *
	 * @param string $downloadUrl URL of the download
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDownload($downloadUrl, $segment = '') {
		return $this->_request('Actions.getDownload', array(
			'downloadUrl' => $downloadUrl,
			'segment' => $segment,
		));
	}

	/**
	 * Get outlinks
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getOutlinks($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('Actions.getOutlinks', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get outlink information
	 *
	 * @param string $outlinkUrl URL of the outlink
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getOutlink($outlinkUrl, $segment = '') {
		return $this->_request('Actions.getOutlink', array(
			'outlinkUrl' => $outlinkUrl,
			'segment' => $segment,
		));
	}

	/**
	 * Get the site search keywords
	 *
	 * @param string $segment
	 */
	public function getSiteSearchKeywords($segment = '') {
		return $this->_request('Actions.getSiteSearchKeywords', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get search keywords with no search results
	 *
	 * @param string $segment
	 */
	public function getSiteSearchNoResultKeywords($segment = '') {
		return $this->_request('Actions.getSiteSearchNoResultKeywords', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get site search categories
	 *
	 * @param string $segment
	 */
	public function getSiteSearchCategories($segment = '') {
		return $this->_request('Actions.getSiteSearchCategories', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: ANNOTATIONS
	 */

	/**
	 * Add annotation
	 *
	 * @param string $note
	 * @param integer $starred
	 */
	public function addAnnotation($note, $starred = 0) {
		return $this->_request('Annotations.add', array(
			'note' => $note,
			'starred' => $starred,
		));
	}

	/**
	 * Save annotation
	 *
	 * @param integer $idNote
	 * @param string $note
	 * @param integer $starred
	 */
	public function saveAnnotation($idNote, $note = '', $starred = '') {
		return $this->_request('Annotations.save', array(
			'idNote' => $idNote,
			'note' => $note,
			'starred' => $starred,
		));
	}

	/**
	 * Delete annotation
	 *
	 * @param integer $idNote
	 */
	public function deleteAnnotation($idNote) {
		return $this->_request('Annotations.delete', array(
			'idNote' => $idNote,
		));
	}

	/**
	 * Delete all annotations
	 */
	public function deleteAllAnnotations() {
		return $this->_request('Annotations.deleteAll');
	}

	/**
	 * Get annotation
	 *
	 * @param integer $idNote
	 */
	public function getAnnotation($idNote) {
		return $this->_request('Annotations.get', array(
			'idNote' => $idNote,
		));
	}

	/**
	 * Get all annotations
	 *
	 * @param integer $lastN
	 */
	public function getAllAnnotation($lastN = '') {
		return $this->_request('Annotations.getAll', array(
			'lastN' => $lastN,
		));
	}

	/**
	 * Get number of annotation for current period
	 *
	 * @param integer $lastN
	 * @param string $getAnnotationText
	 */
	public function getAnnotationCountForDates($lastN, $getAnnotationText) {
		return $this->_request('Annotations.getAnnotationCountForDates', array(
			'lastN' => $lastN,
			'getAnnotationText' => $getAnnotationText
		));
	}

	/**
	 * MODULE: CONTENTS
	 */

	/**
	 * Get content names
	 *
	 * @param string $segment
	 * @param integer $idSubtable
	 */
	public function getContentNames($segment = '', $idSubtable = '') {
		return $this->_request('Contents.getContentNames', array(
			'segment' => $segment,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get content pieces
	 *
	 * @param string $segment
	 * @param integer $idSubtable
	 */
	public function getContentPieces($segment = '', $idSubtable = '') {
		return $this->_request('Contents.getContentPieces', array(
			'segment' => $segment,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * MODULE: CUSTOM ALERTS
	 */

	/**
	 * Get alert details
	 *
	 * @param integer $idAlert
	 */
	public function getAlert($idAlert) {
		return $this->_request('CustomAlerts.getAlert', array(
			'idAlert' => $idAlert,
		));
	}

	/**
	 * Get values for alerts in the past
	 *
	 * @param integer $idAlert
	 * @param unknown $subPeriodN
	 */
	public function getValuesForAlertInPast($idAlert, $subPeriodN) {
		return $this->_request('CustomAlerts.getValuesForAlertInPast', array(
			'idAlert' => $idAlert,
			'subPeriodN' => $subPeriodN,
		));
	}

	/**
	 * Get all alert details
	 *
	 * @param array $idSites Array of site IDs
	 * @param integer $ifSuperUserReturnAllAlerts
	 */
	public function getAlerts($idSites, $ifSuperUserReturnAllAlerts = '') {
		return $this->_request('CustomAlerts.getAlerts', array(
			'idSites' => $idSites,
			'ifSuperUserReturnAllAlerts' => $ifSuperUserReturnAllAlerts,
		));
	}

	/**
	 * Add alert
	 *
	 * @param string $name
	 * @param array $idSites Array of site IDs
	 * @param integer $emailMe
	 * @param unknown $additionalEmails
	 * @param unknown $phoneNumbers
	 * @param unknown $metric
	 * @param unknown $metricCondition
	 * @param unknown $metricValue
	 * @param unknown $comparedTo
	 * @param unknown $reportUniqueId
	 * @param unknown $reportCondition
	 * @param unknown $reportValue
	 */
	public function addAlert($name, $idSites, $emailMe, $additionalEmails, $phoneNumbers, $metric, $metricCondition,
		$metricValue, $comparedTo, $reportUniqueId, $reportCondition = '', $reportValue = '') {
		return $this->_request('CustomAlerts.addAlert', array(
			'name' => $name,
			'idSites' => $idSites,
			'emailMe' => $emailMe,
			'additionalEmails' => $additionalEmails,
			'phoneNumbers' => $phoneNumbers,
			'metric' => $metric,
			'metricCondition' => $metricCondition,
			'metricValue' => $metricValue,
			'comparedTo' => $comparedTo,
			'reportUniqueId' => $reportUniqueId,
			'reportCondition' => $reportCondition,
			'reportValue' => $reportValue,
		));
	}

	/**
	 * Edit alert
	 *
	 * @param integer $idAlert
	 * @param string $name
	 * @param array $idSites Array of site IDs
	 * @param integer $emailMe
	 * @param unknown $additionalEmails
	 * @param unknown $phoneNumbers
	 * @param unknown $metric
	 * @param unknown $metricCondition
	 * @param unknown $metricValue
	 * @param unknown $comparedTo
	 * @param unknown $reportUniqueId
	 * @param unknown $reportCondition
	 * @param unknown $reportValue
	 */
	public function editAlert($idAlert, $name, $idSites, $emailMe, $additionalEmails, $phoneNumbers, $metric, $metricCondition,
		$metricValue, $comparedTo, $reportUniqueId, $reportCondition = '', $reportValue = '') {
		return $this->_request('CustomAlerts.editAlert', array(
			'idAlert' => $idAlert,
			'name' => $name,
			'idSites' => $idSites,
			'emailMe' => $emailMe,
			'additionalEmails' => $additionalEmails,
			'phoneNumbers' => $phoneNumbers,
			'metric' => $metric,
			'metricCondition' => $metricCondition,
			'metricValue' => $metricValue,
			'comparedTo' => $comparedTo,
			'reportUniqueId' => $reportUniqueId,
			'reportCondition' => $reportCondition,
			'reportValue' => $reportValue,
		));
	}

	/**
	 * Delete Alert
	 *
	 * @param integer $idAlert
	 */
	public function deleteAlert($idAlert) {
		return $this->_request('CustomAlerts.deleteAlert', array(
			'idAlert' => $idAlert,
		));
	}

	/**
	 * Get triggered alerts
	 *
	 * @param array $idSites
	 */
	public function getTriggeredAlerts($idSites) {
		return $this->_request('CustomAlerts.getTriggeredAlerts', array(
			'idSites' => $idSites,
		));
	}

	/**
	 * MODULE: CUSTOM VARIABLES
	 * Custom variable information
	 */

	/**
	 * Get custom variables
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getCustomVariables($segment = '', $expanded = '') {
		return $this->_request('CustomVariables.getCustomVariables', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get information about a custom variable
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getCustomVariable($idSubtable, $segment = '') {
		return $this->_request('CustomVariables.getCustomVariablesValuesFromNameId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: Dashboard
	 */

	/**
	 * Get list of dashboards
	 */
	public function getDashboards() {
		return $this->_request('Dashboard.getDashboards');
	}

	/**
	 * MODULE: DEVICES DETECTION
	 */

	/**
	 * Get Device Type.
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDeviceType($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('DevicesDetection.getType', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get Device Brand.
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDeviceBrand($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('DevicesDetection.getBrand', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get Device Model.
	 *
	 * @param string $segment
	 * @param string $expanded
	 * @param int $idSubtable
	 */
	public function getDeviceModel($segment = '', $expanded = '', $idSubtable = '') {
		return $this->_request('DevicesDetection.getModel', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get operating system families
	 *
	 * @param string $segment
	 */
	public function getOSFamilies($segment = '') {
		return $this->_request('DevicesDetection.getOsFamilies', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get os versions
	 *
	 * @param string $segment
	 */
	public function getOsVersions($segment = '') {
		return $this->_request('DevicesDetection.getOsVersions', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get browsers
	 *
	 * @param string $segment
	 */
	public function getBrowsers($segment = '') {
		return $this->_request('DevicesDetection.getBrowsers', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get browser versions
	 *
	 * @param string $segment
	 */
	public function getBrowserVersions($segment = '') {
		return $this->_request('DevicesDetection.getBrowserVersions', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get browser engines
	 *
	 * @param string $segment
	 */
	public function getBrowserEngines($segment = '') {
		return $this->_request('DevicesDetection.getBrowserEngines', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: EVENTS
	 */

	/**
	 * Get event categories
	 *
	 * @param string $segment
	 * @param unknown $expanded
	 * @param string $secondaryDimension ('eventAction' or 'eventName')
	 */
	public function getEventCategory($segment = '', $expanded = '', $secondaryDimension = '') {
		return $this->_request('Events.getCategory', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'secondaryDimension' => $secondaryDimension,
		));
	}

	/**
	 * Get event actions
	 *
	 * @param string $segment
	 * @param unknown $expanded
	 * @param string $secondaryDimension ('eventName' or 'eventCategory')
	 */
	public function getEventAction($segment = '', $expanded = '', $secondaryDimension = '') {
		return $this->_request('Events.getAction', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'secondaryDimension' => $secondaryDimension,
		));
	}

	/**
	 * Get event names
	 *
	 * @param string $segment
	 * @param unknown $expanded
	 * @param string $secondaryDimension ('eventAction' or 'eventCategory')
	 */
	public function getEventName($segment = '', $expanded = '', $secondaryDimension = '') {
		return $this->_request('Events.getName', array(
			'segment' => $segment,
			'expanded' => $expanded,
			'secondaryDimension' => $secondaryDimension,
		));
	}

	/**
	 * Get action from category ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getActionFromCategoryId($idSubtable, $segment = '') {
		return $this->_request('Events.getActionFromCategoryId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get name from category ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getNameFromCategoryId($idSubtable, $segment = '') {
		return $this->_request('Events.getNameFromCategoryId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get category from action ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getCategoryFromActionId($idSubtable, $segment = '') {
		return $this->_request('Events.getCategoryFromActionId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get name from action ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getNameFromActionId($idSubtable, $segment = '') {
		return $this->_request('Events.getNameFromActionId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get action from name ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getActionFromNameId($idSubtable, $segment = '') {
		return $this->_request('Events.getActionFromNameId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get category from name ID
	 *
	 * @param integer $idSubtable
	 * @param string $segment
	 */
	public function getCategoryFromNameId($idSubtable, $segment = '') {
		return $this->_request('Events.getCategoryFromNameId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: EXAMPLE API
	 * Get api and piwiki information
	 */

	/**
	 * Get the piwik version
	 */
	public function getExamplePiwikVersion() {
		return $this->_request('ExampleAPI.getPiwikVersion');
	}

	/**
	 * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
	 */
	public function getExampleAnswerToLife() {
		return $this->_request('ExampleAPI.getAnswerToLife');
	}

	/**
	 * Unknown
	 */
	public function getExampleObject() {
		return $this->_request('ExampleAPI.getObject');
	}

	/**
	 * Get the sum of the parameters
	 *
	 * @param int $a
	 * @param int $b
	 */
	public function getExampleSum($a = '0', $b = '0') {
		return $this->_request('ExampleAPI.getSum', array(
			'a' => $a,
			'b' => $b,
		));
	}

	/**
	 * Returns nothing but the success of the request
	 */
	public function getExampleNull() {
		return $this->_request('ExampleAPI.getNull');
	}

	/**
	 * Get a short piwik description
	 */
	public function getExampleDescriptionArray() {
		return $this->_request('ExampleAPI.getDescriptionArray');
	}

	/**
	 * Get a short comparison with other analytic software
	 */
	public function getExampleCompetitionDatatable() {
		return $this->_request('ExampleAPI.getCompetitionDatatable');
	}

	/**
	 * Get information about 42
	 * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
	 */
	public function getExampleMoreInformationAnswerToLife() {
		return $this->_request('ExampleAPI.getMoreInformationAnswerToLife');
	}

	/**
	 * Get a multidimensional array
	 */
	public function getExampleMultiArray() {
		return $this->_request('ExampleAPI.getMultiArray');
	}

	/**
	 * MODULE: EXAMPLE PLUGIN
	 */

	/**
	 * Get a multidimensional array
	 *
	 * @param integer $truth
	 */
	public function getExamplePluginAnswerToLife($truth = 1) {
		return $this->_request('ExamplePlugin.getAnswerToLife', array(
			'truth' => $truth,
		));
	}

	/**
	 * Get a multidimensional array
	 *
	 * @param string $segment
	 */
	public function getExamplePluginReport($segment = '') {
		return $this->_request('ExamplePlugin.getExampleReport', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: FEEDBACK
	 */

	/**
	 * Get a multidimensional array
	 *
	 * @param string $featureName
	 * @param unknown $like
	 * @param string $message
	 */
	public function sendFeedbackForFeature($featureName, $like, $message = '') {
		return $this->_request('Feedback.sendFeedbackForFeature', array(
			'featureName' => $featureName,
			'like' => $like,
			'message' => $message,
		));
	}

	/**
	 * MODULE: GOALS
	 * Handle goals
	 */

	/**
	 * Get all goals
	 */
	public function getGoals() {
		return $this->_request('Goals.getGoals');
	}

	/**
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

	/**
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

	/**
	 * Delete a goal
	 *
	 * @param int $idGoal
	 */
	public function deleteGoal($idGoal) {
		return $this->_request('Goals.deleteGoal', array(
			'idGoal' => $idGoal,
		));
	}

	/**
	 * Get the SKU of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsSku($abandonedCarts) {
		return $this->_request('Goals.getItemsSku', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}

	/**
	 * Get the name of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsName($abandonedCarts) {
		return $this->_request('Goals.getItemsName', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}

	/**
	 * Get the categories of the items
	 *
	 * @param boolean abandonedCarts
	 */
	public function getItemsCategory($abandonedCarts) {
		return $this->_request('Goals.getItemsCategory', array(
			'abandonedCarts' => $abandonedCarts,
		));
	}

	/**
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


	/**
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

	/**
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

	/**
	 * MODULE: IMAGE GRAPH
	 * Generate png graphs
	 */

	const GRAPH_EVOLUTION = 'evolution';
	const GRAPH_VERTICAL_BAR = 'verticalBar';
	const GRAPH_PIE = 'pie';
	const GRAPH_PIE_3D = '3dPie';

	/**
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
	 * @param string $columns
	 * @param boolean $showMetricTitle
	 * @param int $width
	 * @param int $height
	 * @param int $fontSize
	 * @param boolean $aliasedGraph "by default, Graphs are "smooth" (anti-aliased). If you are generating hundreds of graphs and are concerned with performance, you can set aliasedGraph=0. This will disable anti aliasing and graphs will be generated faster, but look less pretty."
	 * @param array $colors Use own colors instead of the default. The colors has to be in hexadecimal value without '#'
	 */
	public function getImageGraph($apiModule, $apiAction, $graphType = '', $outputType = '0', $columns = '', $labels = '', $showLegend = '1', $width = '', $height = '', $fontSize = '9', $legendFontSize = '', $aliasedGraph = '1', $idGoal = '', $colors = array()) {
		return $this->_request('ImageGraph.get', array(
			'apiModule' => $apiModule,
			'apiAction' => $apiAction,
			'graphType' => $graphType,
			'outputType' => $outputType,
			'columns' => $columns,
			'labels' => $labels,
			'showLegend' => $showLegend,
			'width' => $width,
			'height' => $height,
			'fontSize' => $fontSize,
			'legendFontSize' => $legendFontSize,
			'aliasedGraph' => $aliasedGraph,
			'idGoal ' => $idGoal,
			'colors' => $colors,
		));
	}

	/**
	 * MODULE: LANGUAGES MANAGER
	 * Get plugin insights
	 */

	/**
	 * Check if piwik can generate insights for current period
	 */
	public function canGenerateInsights() {
		return $this->_request('Insights.canGenerateInsights');
	}

	/**
	 * Get insights overview
	 *
	 * @param string $segment
	 */
	public function getInsightsOverview($segment) {
		return $this->_request('Insights.getInsightsOverview', array(
			'segment' => $segment,
		));
	}

	/**
	 * Unknown
	 *
	 * @param string $segment
	 */
	public function getMoversAndShakersOverview($segment) {
		return $this->_request('Insights.getMoversAndShakersOverview', array(
			'segment' => $segment,
		));
	}

	/**
	 * Unknown
	 *
	 * @param integer $reportUniqueId
	 * @param string $segment
	 * @param integer $comparedToXPeriods
	 * @param integer $limitIncreaser
	 * @param integer $limitDecreaser
	 */
	public function getMoversAndShakers($reportUniqueId, $segment, $comparedToXPeriods = '1', $limitIncreaser = '4',
		$limitDecreaser = '4') {
		return $this->_request('Insights.getMoversAndShakers', array(
			'reportUniqueId' => $reportUniqueId,
			'segment' => $segment,
			'comparedToXPeriods' => $comparedToXPeriods,
			'limitIncreaser' => $limitIncreaser,
			'limitDecreaser' => $limitDecreaser,
		));
	}

	/**
	 * Unknown
	 *
	 * @param integer $reportUniqueId
	 * @param string $segment
	 * @param integer $limitIncreaser
	 * @param integer $limitDecreaser
	 * @param string $filterBy
	 * @param integer minImpactPercent (0-100)
	 * @param integer minGrowthPercent (0-100)
	 * @param integer $comparedToXPeriods
	 * @param string $orderBy
	 */
	public function getInsights($reportUniqueId, $segment, $limitIncreaser = '5', $limitDecreaser = '5', $filterBy = '',
		$minImpactPercent = '2', $minGrowthPercent = '20', $comparedToXPeriods = '1', $orderBy = 'absolute') {
		return $this->_request('Insights.getInsights', array(
			'reportUniqueId' => $reportUniqueId,
			'segment' => $segment,
			'limitIncreaser' => $limitIncreaser,
			'limitDecreaser' => $limitDecreaser,
			'filterBy' => $filterBy,
			'minImpactPercent' => $minImpactPercent,
			'minGrowthPercent' => $minGrowthPercent,
			'comparedToXPeriods' => $comparedToXPeriods,
			'orderBy' => $orderBy,
		));
	}

	/**
	 * MODULE: LANGUAGES MANAGER
	 * Manage languages
	 */

	/**
	 * Proof if language is available
	 *
	 * @param string $languageCode
	 */
	public function getLanguageAvailable($languageCode) {
		return $this->_request('LanguagesManager.isLanguageAvailable', array(
			'languageCode' => $languageCode,
		));
	}

	/**
	 * Get all available languages
	 */
	public function getAvailableLanguages() {
		return $this->_request('LanguagesManager.getAvailableLanguages');
	}

	/**
	 * Get all available languages with information
	 */
	public function getAvailableLanguagesInfo() {
		return $this->_request('LanguagesManager.getAvailableLanguagesInfo');
	}

	/**
	 * Get all available languages with their names
	 */
	public function getAvailableLanguageNames() {
		return $this->_request('LanguagesManager.getAvailableLanguageNames');
	}

	/**
	 * Get translations for a language
	 *
	 * @param string $languageCode
	 */
	public function getTranslations($languageCode) {
		return $this->_request('LanguagesManager.getTranslationsForLanguage', array(
			'languageCode' => $languageCode,
		));
	}

	/**
	 * Get the language for the user with the login $login
	 *
	 * @param string $login
	 */
	public function getLanguageForUser($login) {
		return $this->_request('LanguagesManager.getLanguageForUser', array(
			'login' => $login,
		));
	}

	/**
	 * Set the language for the user with the login $login
	 *
	 * @param string $login
	 * @param string $languageCode
	 */
	public function setLanguageForUser($login, $languageCode) {
		return $this->_request('LanguagesManager.setLanguageForUser', array(
			'login' => $login,
			'languageCode' => $languageCode,
		));
	}


	/**
	 * MODULE: LIVE
	 * Request live data
	 */

	/**
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

	/**
	 * Get information about the last visits
	 *
	 * @param string $segment
	 * @param int $filterLimit
	 * @param int $maxIdVisit
	 * @param string $minTimestamp
	 */
	public function getLastVisitsDetails($segment = '', $filter_limit = '', $maxIdVisit = '', $minTimestamp = '') {
		return $this->_request('Live.getLastVisitsDetails', array(
			'segment' => $segment,
			'filter_limit' => $filter_limit,
			'maxIdVisit' => $maxIdVisit,
			'minTimestamp' => $minTimestamp,
		));
	}

	/**
	 * Get a profile for a visitor
	 *
	 * @param integer $visitorId
	 * @param string $segment
	 */
	public function getVisitorProfile($visitorId = '', $segment = '') {
		return $this->_request('Live.getVisitorProfile', array(
			'visitorId' => $visitorId,
			'segment' => $segment,
		));
	}

	/**
	 * Get the ID of the most recent visitor
	 *
	 * @param string $segment
	 */
	public function getMostRecentVisitorId($segment = '') {
		return $this->_request('Live.getMostRecentVisitorId', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: MOBILEMESSAGING
	 * The MobileMessaging API lets you manage and access all the MobileMessaging plugin features including : - manage SMS API credential - activate phone numbers - check remaining credits - send SMS
	 */

	/**
	 * Checks if SMSAPI has been configured
	 *
	 * @return mixed
	 */
	public function areSMSAPICredentialProvided() {
		return $this->_request('MobileMessaging.areSMSAPICredentialProvided');
	}

	/**
	 * Get list with sms provider
	 *
	 * @return mixed
	 */
	public function getSMSProvider() {
		return $this->_request('MobileMessaging.getSMSProvider');
	}

	/**
	 * Set SMSAPI credentials
	 *
	 * @param string $provider
	 * @param string $apiKey
	 * @return mixed
	 */
	public function setSMSAPICredential($provider, $apiKey) {
		return $this->_request('MobileMessaging.setSMSAPICredential', array(
			'provider' => $provider,
			'apiKey' => $apiKey,
		));
	}

	/**
	 * Add phone number
	 *
	 * @param string $phoneNumber
	 * @return mixed
	 */
	public function addPhoneNumber($phoneNumber) {
		return $this->_request('MobileMessaging.addPhoneNumber', array(
			'phoneNumber' => $phoneNumber,
		));
	}

	/**
	 * Get credits left
	 *
	 * @return mixed
	 */
	public function getCreditLeft() {
		return $this->_request('MobileMessaging.getCreditLeft');
	}

	/**
	 * Remove phone number
	 *
	 * @param string $phoneNumber
	 * @return mixed
	 */
	public function removePhoneNumber($phoneNumber) {
		return $this->_request('MobileMessaging.removePhoneNumber', array(
			'phoneNumber' => $phoneNumber,
		));
	}

	/**
	 * Validate phone number
	 *
	 * @param string $phoneNumber
	 * @param string $verificationCode
	 * @return mixed
	 */
	public function validatePhoneNumber($phoneNumber, $verificationCode) {
		return $this->_request('MobileMessaging.validatePhoneNumber', array(
			'phoneNumber' => $phoneNumber,
			'verificationCode' => $verificationCode,
		));
	}

	/**
	 * Delete SMSAPI credentials
	 *
	 * @return mixed
	 */
	public function deleteSMSAPICredential() {
		return $this->_request('MobileMessaging.deleteSMSAPICredential');
	}

	/**
	 * Set unknown
	 *
	 * @param $delegatedManagement
	 * @return mixed
	 */
	public function setDelegatedManagement($delegatedManagement) {
		return $this->_request('MobileMessaging.setDelegatedManagement', array(
			'delegatedManagement' => $delegatedManagement,
		));
	}

	/**
	 * Get unknown
	 *
	 * @return mixed
	 */
	public function getDelegatedManagement() {
		return $this->_request('MobileMessaging.getDelegatedManagement');
	}


	/**
	 * MODULE: MULTI SITES
	 * Get information about multiple sites
	 */

	/**
	 * Get information about multiple sites
	 *
	 * @param string $segment
	 * @param string $enhanced
	 */
	public function getMultiSites($segment = '', $enhanced = '') {
		return $this->_request('MultiSites.getAll', array(
			'segment' => $segment,
			'enhanced' => $enhanced,
		));
	}

	/**
	 * Get key metrics about one of the sites the user manages
	 *
	 * @param string $segment
	 * @param string $enhanced
	 */
	public function getOne($segment = '', $enhanced = '') {
		return $this->_request('MultiSites.getOne', array(
			'segment' => $segment,
			'enhanced' => $enhanced,
		));
	}

	/**
	 * MODULE: OVERLAY
	 */

	/**
	 * Unknown
	 */
	public function getOverlayTranslations() {
		return $this->_request('Overlay.getTranslations');
	}

	/**
	 * Unknown
	 */
	public function getOverlayExcludedQueryParameters() {
		return $this->_request('Overlay.getExcludedQueryParameters');
	}

	/**
	 * Unknown
	 */
	public function getOverlayFollowingPages() {
		return $this->_request('Overlay.getFollowingPages');
	}

	/**
	 * MODULE: PROVIDER
	 * Get provider information
	 */

	/**
	 * Get information about visitors internet providers
	 *
	 * @param string $segment
	 */
	public function getProvider($segment = '') {
		return $this->_request('Provider.getProvider', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: REFERERS
	 * Get information about the referrers
	 */

	/**
	 * Get referrer types
	 *
	 * @param string $segment
	 * @param string $typeReferrer
	 */
	public function getReferrerType($segment = '', $typeReferrer = '') {
		return $this->_request('Referrers.getReferrerType', array(
			'segment' => $segment,
			'typeReferrer' => $typeReferrer,
		));
	}

	/**
	 * Get all referrers
	 *
	 * @param string $segment
	 */
	public function getAllReferrers($segment = '') {
		return $this->_request('Referrers.getAll', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get referrer keywords
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getKeywords($segment = '', $expanded = '') {
		return $this->_request('Referrers.getKeywords', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get keywords for an url
	 *
	 * @param string $url
	 */
	public function getKeywordsForPageUrl($url) {
		return $this->_request('Referrers.getKeywordsForPageUrl', array(
			'url' => $url,
		));
	}

	/**
	 * Get keywords for an page title
	 *
	 * @param string $title
	 */
	public function getKeywordsForPageTitle($title) {
		return $this->_request('Referrers.getKeywordsForPageTitle', array(
			'title' => $title,
		));
	}

	/**
	 * Get search engines by keyword
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getSearchEnginesFromKeywordId($idSubtable, $segment = '') {
		return $this->_request('Referrers.getSearchEnginesFromKeywordId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get search engines
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getSearchEngines($segment = '', $expanded = '') {
		return $this->_request('Referrers.getSearchEngines', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get search engines by search engine ID
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getKeywordsFromSearchEngineId($idSubtable, $segment = '') {
		return $this->_request('Referrers.getKeywordsFromSearchEngineId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get campaigns
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getCampaigns($segment = '', $expanded = '') {
		return $this->_request('Referrers.getCampaigns', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get keywords by campaign ID
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getKeywordsFromCampaignId($idSubtable, $segment = '') {
		return $this->_request('Referrers.getKeywordsFromCampaignId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get website referrerals
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getWebsites($segment = '', $expanded = '') {
		return $this->_request('Referrers.getWebsites', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get urls by website ID
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getUrlsFromWebsiteId($idSubtable, $segment = '') {
		return $this->_request('Referrers.getUrlsFromWebsiteId', array(
			'idSubtable' => $idSubtable,
			'segment' => $segment,
		));
	}

	/**
	 * Get social referrerals
	 *
	 * @param string $segment
	 * @param string $expanded
	 */
	public function getSocials($segment = '', $expanded = '') {
		return $this->_request('Referrers.getSocials', array(
			'segment' => $segment,
			'expanded' => $expanded,
		));
	}

	/**
	 * Get social referral urls
	 *
	 * @param int $idSubtable
	 * @param string $segment
	 */
	public function getUrlsForSocial($segment = '', $idSubtable = '') {
		return $this->_request('Referrers.getUrlsForSocial', array(
			'segment' => $segment,
			'idSubtable' => $idSubtable,
		));
	}

	/**
	 * Get the number of distinct search engines
	 *
	 * @param string $segment
	 */
	public function getNumberOfSearchEngines($segment = '') {
		return $this->_request('Referrers.getNumberOfDistinctSearchEngines', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of distinct keywords
	 *
	 * @param string $segment
	 */
	public function getNumberOfKeywords($segment = '') {
		return $this->_request('Referrers.getNumberOfDistinctKeywords', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of distinct campaigns
	 *
	 * @param string $segment
	 */
	public function getNumberOfCampaigns($segment = '') {
		return $this->_request('Referrers.getNumberOfDistinctCampaigns', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of distinct websites
	 *
	 * @param string $segment
	 */
	public function getNumberOfWebsites($segment = '') {
		return $this->_request('Referrers.getNumberOfDistinctWebsites', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of distinct websites urls
	 *
	 * @param string $segment
	 */
	public function getNumberOfWebsitesUrls($segment = '') {
		return $this->_request('Referrers.getNumberOfDistinctWebsitesUrls', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: SEO
	 * Get SEO information
	 */

	/**
	 * Get the SEO rank of an url
	 *
	 * @param string $url
	 */
	public function getSeoRank($url) {
		return $this->_request('SEO.getRank', array(
			'url' => $url,
		));
	}

	/**
	 * MODULE: SCHEDULED REPORTS
	 * Manage pdf reports
	 */

	/**
	 * Add scheduled report
	 *
	 * @param string $description
	 * @param string $period
	 * @param string $hour
	 * @param string $reportType
	 * @param string $reportFormat
	 * @param array $reports
	 * @param string $parameters
	 * @param integer $idSegment
	 */
	public function addReport($description, $period, $hour, $reportType, $reportFormat, $reports, $parameters,
		$idSegment = '') {
		return $this->_request('ScheduledReports.addReport', array(
			'description' => $description,
			'period' => $period,
			'hour' => $hour,
			'reportType' => $reportType,
			'reportFormat' => $reportFormat,
			'reports' => $reports,
			'parameters' => $parameters,
			'idSegment' => $idSegment,
		));
	}

	/**
	 * Updated scheduled report
	 *
	 * @param integer $idReport
	 * @param string $description
	 * @param string $period
	 * @param string $hour
	 * @param string $reportType
	 * @param string $reportFormat
	 * @param array $reports
	 * @param string $parameters
	 * @param integer $idSegment
	 */
	public function updateReport($idReport, $description, $period, $hour, $reportType, $reportFormat, $reports, $parameters,
		$idSegment = '') {
		return $this->_request('ScheduledReports.updateReport', array(
			'idReport' => $idReport,
			'description' => $description,
			'period' => $period,
			'hour' => $hour,
			'reportType' => $reportType,
			'reportFormat' => $reportFormat,
			'reports' => $reports,
			'parameters' => $parameters,
			'idSegment' => $idSegment,
		));
	}

	/**
	 * Delete scheduled report
	 *
	 * @param integer $idReport
	 */
	public function deleteReport($idReport) {
		return $this->_request('ScheduledReports.deleteReport', array(
			'idReport' => $idReport,
		));
	}

	/**
	 * Get list of scheduled reports
	 *
	 * @param integer $idReport
	 * @param integer $ifSuperUserReturnOnlySuperUserReports
	 * @param integer $idSegment
	 */
	public function getReports($idReport = '', $ifSuperUserReturnOnlySuperUserReports = '', $idSegment = '') {
		return $this->_request('ScheduledReports.getReports', array(
			'idReport' => $idReport,
			'ifSuperUserReturnOnlySuperUserReports' => $ifSuperUserReturnOnlySuperUserReports,
			'idSegment' => $idSegment,
		));
	}

	/**
	 * Get list of scheduled reports
	 *
	 * @param integer $idReport
	 * @param integer $language
	 * @param integer $outputType
	 * @param string $reportFormat
	 * @param array $parameters
	 */
	public function generateReport($idReport, $language = '', $outputType = '', $reportFormat = '', $parameters = '') {
		return $this->_request('ScheduledReports.generateReport', array(
			'idReport' => $idReport,
			'language' => $language,
			'outputType' => $outputType,
			'reportFormat' => $reportFormat,
			'parameters' => $parameters,
		));
	}

	/**
	 * Send scheduled reports
	 *
	 * @param integer $idReport
	 * @param integer $force
	 */
	public function sendReport($idReport, $force = '') {
		return $this->_request('ScheduledReports.sendReport', array(
			'idReport' => $idReport,
			'force' => $force,
		));
	}

	/**
	 * MODULE: SEGMENT EDITOR
	 */

	/**
	 * Check if current user can add new segments
	 */
	public function isUserCanAddNewSegment() {
		return $this->_request('SegmentEditor.isUserCanAddNewSegment');
	}

	/**
	 * Delete a segment
	 *
	 * @param integer $idSegment
	 */
	public function deleteSegment($idSegment) {
		return $this->_request('SegmentEditor.delete', array(
			'idSegment' => $idSegment,
		));
	}

	/**
	 * Updates a segment
	 *
	 * @param integer $idSegment
	 * @param string $name
	 * @param string $definition
	 * @param integer $autoArchive
	 * @param integer $enableAllUsers
	 */
	public function updateSegment($idSegment, $name, $definition, $autoArchive = '', $enableAllUsers = '') {
		return $this->_request('SegmentEditor.update', array(
			'idSegment' => $idSegment,
			'name' => $name,
			'definition' => $definition,
			'autoArchive' => $autoArchive,
			'enableAllUsers' => $enableAllUsers,
		));
	}

	/**
	 * Updates a segment
	 *
	 * @param string $name
	 * @param string $definition
	 * @param integer $autoArchive
	 * @param integer $enableAllUsers
	 */
	public function addSegment($name, $definition, $autoArchive = '', $enableAllUsers = '') {
		return $this->_request('SegmentEditor.add', array(
			'name' => $name,
			'definition' => $definition,
			'autoArchive' => $autoArchive,
			'enableAllUsers' => $enableAllUsers,
		));
	}

	/**
	 * Get a segment
	 *
	 * @param integer $idSegment
	 */
	public function getSegment($idSegment) {
		return $this->_request('SegmentEditor.get', array(
			'idSegment' => $idSegment,
		));
	}

	/**
	 * Get all segments
	 */
	public function getAllSegments() {
		return $this->_request('SegmentEditor.getAll');
	}

	/**
	 * MODULE: SITES MANAGER
	 * Manage sites
	 */

	/**
	 * Get the JS tag of the current site
	 *
	 * @param string $piwikUrl
	 * @param integer $mergeSubdomains
	 * @param integer $groupPageTitlesByDomain
	 * @param integer $mergeAliasUrls
	 * @param integer $visitorCustomVariables
	 * @param integer $pageCustomVariables
	 * @param integer $customCampaignNameQueryParam
	 * @param integer $customCampaignKeywordParam
	 * @param integer $doNotTrack
	 * @param integer $disableCookies
	 */
	public function getJavascriptTag($piwikUrl, $mergeSubdomains = '', $groupPageTitlesByDomain = '',
		$mergeAliasUrls = '', $visitorCustomVariables = '', $pageCustomVariables = '',
		$customCampaignNameQueryParam = '', $customCampaignKeywordParam = '', $doNotTrack = '', $disableCookies = '') {
		return $this->_request('SitesManager.getJavascriptTag', array(
			'piwikUrl' => $piwikUrl,
			'mergeSubdomains' => $mergeSubdomains,
			'groupPageTitlesByDomain' => $groupPageTitlesByDomain,
			'mergeAliasUrls' => $mergeAliasUrls,
			'visitorCustomVariables' => $visitorCustomVariables,
			'pageCustomVariables' => $pageCustomVariables,
			'customCampaignNameQueryParam' => $customCampaignNameQueryParam,
			'customCampaignKeywordParam' => $customCampaignKeywordParam,
			'doNotTrack' => $doNotTrack,
			'disableCookies' => $disableCookies,
		));
	}

	/**
	 * Get image tracking code of the current site
	 *
	 * @param string $piwikUrl
	 * @param integer $actionName
	 * @param integer $idGoal
	 * @param integer $revenue
	 */
	public function getImageTrackingCode($piwikUrl, $actionName = '', $idGoal = '',
		$revenue = '') {
		return $this->_request('SitesManager.getImageTrackingCode', array(
			'piwikUrl' => $piwikUrl,
			'actionName' => $actionName,
			'idGoal' => $idGoal,
			'revenue' => $revenue,
		));
	}

	/**
	 * Get sites from a group
	 *
	 * @param string $group
	 */
	public function getSitesFromGroup($group) {
		return $this->_request('SitesManager.getSitesFromGroup', array(
			'group' => $group,
		));
	}

	/**
	 * Get all site groups
	 */
	public function getSitesGroups() {
		return $this->_request('SitesManager.getSitesGroups');
	}

	/**
	 * Get information about the current site
	 */
	public function getSiteInformation() {
		return $this->_request('SitesManager.getSiteFromId');
	}

	/**
	 * Get urls from current site
	 */
	public function getSiteUrls() {
		return $this->_request('SitesManager.getSiteUrlsFromId');
	}

	/**
	 * Get all sites
	 */
	public function getAllSites() {
		return $this->_request('SitesManager.getAllSites');
	}

	/**
	 * Get all sites with ID
	 */
	public function getAllSitesId() {
		return $this->_request('SitesManager.getAllSitesId');
	}

	/**
	 * Get all sites with the visit count since $timestamp
	 *
	 * @param string $timestamp
	 */
	public function getSitesIdWithVisits($timestamp) {
		return $this->_request('SitesManager.getSitesIdWithVisits', array(
			'timestamp' => $timestamp,
		));
	}

	/**
	 * Get all sites where the current user has admin access
	 */
	public function getSitesWithAdminAccess() {
		return $this->_request('SitesManager.getSitesWithAdminAccess');
	}

	/**
	 * Get all sites where the current user has view access
	 */
	public function getSitesWithViewAccess() {
		return $this->_request('SitesManager.getSitesWithViewAccess');
	}

	/**
	 * Get all sites where the current user has a least view access
	 *
	 * @param int $limit
	 */
	public function getSitesWithAtLeastViewAccess($limit = '') {
		return $this->_request('SitesManager.getSitesWithAtLeastViewAccess', array(
			'limit' => $limit,
		));
	}

	/**
	 * Get all sites with ID where the current user has admin access
	 */
	public function getSitesIdWithAdminAccess() {
		return $this->_request('SitesManager.getSitesIdWithAdminAccess');
	}

	/**
	 * Get all sites with ID where the current user has view access
	 */
	public function getSitesIdWithViewAccess() {
		return $this->_request('SitesManager.getSitesIdWithViewAccess');
	}

	/**
	 * Get all sites with ID where the current user has at least view access
	 */
	public function getSitesIdWithAtLeastViewAccess() {
		return $this->_request('SitesManager.getSitesIdWithAtLeastViewAccess');
	}

	/**
	 * Get a site by it's URL
	 *
	 * @param string $url
	 */
	public function getSitesIdFromSiteUrl($url) {
		return $this->_request('SitesManager.getSitesIdFromSiteUrl', array(
			'url' => $url,
		));
	}

	/**
	 * Add a site
	 *
	 * @param string $siteName
	 * @param array $urls
	 * @param boolean $ecommerce
	 * @param boolean $siteSearch
	 * @param string $searchKeywordParameters
	 * @param string $searchCategoryParameters
	 * @param array $excludeIps
	 * @param array $excludedQueryParameters
	 * @param string $timezone
	 * @param string $currency
	 * @param string $group
	 * @param string $startDate
	 * @param string $excludedUserAgents
	 * @param string $keepURLFragments
	 * @param string $type
	 */
	public function addSite($siteName, $urls, $ecommerce = '', $siteSearch = '', $searchKeywordParameters = '',
		$searchCategoryParameters = '', $excludeIps = '', $excludedQueryParameters = '', $timezone = '', $currency = '',
		$group = '', $startDate = '', $excludedUserAgents = '', $keepURLFragments = '', $type = '') {
		return $this->_request('SitesManager.addSite', array(
			'siteName' => $siteName,
			'urls' => $urls,
			'ecommerce' => $ecommerce,
			'siteSearch' => $siteSearch,
			'searchKeywordParameters' => $searchKeywordParameters,
			'searchCategoryParameters' => $searchCategoryParameters,
			'excludeIps' => $excludeIps,
			'excludedQueryParameters' => $excludedQueryParameters,
			'timezone' => $timezone,
			'currency' => $currency,
			'group' => $group,
			'startDate' => $startDate,
			'excludedUserAgents' => $excludedUserAgents,
			'keepURLFragments' => $keepURLFragments,
			'type' => $type,
		));
	}

	/**
	 * Delete current site
	 */
	public function deleteSite() {
		return $this->_request('SitesManager.deleteSite');
	}

	/**
	 * Add alias urls for the current site
	 *
	 * @param array $urls
	 */
	public function addSiteAliasUrls($urls) {
		return $this->_request('SitesManager.addSiteAliasUrls', array(
			'urls' => $urls,
		));
	}

	/**
	 * Set alias urls for the current site
	 *
	 * @param array $urls
	 */
	public function setSiteAliasUrls($urls) {
		return $this->_request('SitesManager.setSiteAliasUrls', array(
			'urls' => $urls,
		));
	}

	/**
	 * Get IP's for a specific range
	 *
	 * @param string $ipRange
	 */
	public function getIpsForRange($ipRange) {
		return $this->_request('SitesManager.getIpsForRange', array(
			'ipRange' => $ipRange,
		));
	}

	/**
	 * Set the global excluded IP's
	 *
	 * @param array $excludedIps
	 */
	public function setExcludedIps($excludedIps) {
		return $this->_request('SitesManager.setGlobalExcludedIps', array(
			'excludedIps' => $excludedIps,
		));
	}

	/**
	 * Set global search parameters
	 *
	 * @param $searchKeywordParameters
	 * @param $searchCategoryParameters
	 * @return mixed
	 */
	public function setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters) {
		return $this->_request('SitesManager.setGlobalSearchParameters ', array(
			'searchKeywordParameters' => $searchKeywordParameters,
			'searchCategoryParameters' => $searchCategoryParameters,
		));
	}

	/**
	 * Get search keywords
	 */
	public function getSearchKeywordParametersGlobal() {
		return $this->_request('SitesManager.getSearchKeywordParametersGlobal  ');
	}

	/**
	 * Get search categories
	 */
	public function getSearchCategoryParametersGlobal() {
		return $this->_request('SitesManager.getSearchCategoryParametersGlobal ');
	}

	/**
	 * Get the global excluded query parameters
	 */
	public function getExcludedParameters() {
		return $this->_request('SitesManager.getExcludedQueryParametersGlobal');
	}

	/**
	 * Get the global excluded user agents
	 */
	public function getExcludedUserAgentsGlobal() {
		return $this->_request('SitesManager.getExcludedUserAgentsGlobal');
	}

	/**
	 * Set the global excluded user agents
	 *
	 * @param array $excludedUserAgents
	 */
	public function setGlobalExcludedUserAgents($excludedUserAgents) {
		return $this->_request('SitesManager.setGlobalExcludedUserAgents', array(
			'excludedUserAgents' => $excludedUserAgents,
		));
	}

	/**
	 * Check if site specific user agent exclude is enabled
	 */
	public function isSiteSpecificUserAgentExcludeEnabled() {
		return $this->_request('SitesManager.isSiteSpecificUserAgentExcludeEnabled');
	}

	/**
	 * Set site specific user agent exclude
	 *
	 * @param integer $enabled
	 */
	public function setSiteSpecificUserAgentExcludeEnabled($enabled) {
		return $this->_request('SitesManager.setSiteSpecificUserAgentExcludeEnabled', array(
			'enabled' => $enabled,
		));
	}

	/**
	 * Check if the url fragments should be global
	 */
	public function getKeepURLFragmentsGlobal() {
		return $this->_request('SitesManager.getKeepURLFragmentsGlobal');
	}

	/**
	 * Set the url fragments global
	 *
	 * @param integer $enabled
	 */
	public function setKeepURLFragmentsGlobal($enabled) {
		return $this->_request('SitesManager.setKeepURLFragmentsGlobal', array(
			'enabled' => $enabled,
		));
	}

	/**
	 * Set the global excluded query parameters
	 *
	 * @param array $excludedQueryParameters
	 */
	public function setExcludedParameters($excludedQueryParameters) {
		return $this->_request('SitesManager.setGlobalExcludedQueryParameters', array(
			'excludedQueryParameters' => $excludedQueryParameters,
		));
	}

	/**
	 * Get the global excluded IP's
	 */
	public function getExcludedIps() {
		return $this->_request('SitesManager.getExcludedIpsGlobal');
	}


	/**
	 * Get the default currency
	 */
	public function getDefaultCurrency() {
		return $this->_request('SitesManager.getDefaultCurrency');
	}

	/**
	 * Set the default currency
	 *
	 * @param string $defaultCurrency
	 */
	public function setDefaultCurrency($defaultCurrency) {
		return $this->_request('SitesManager.setDefaultCurrency', array(
			'defaultCurrency' => $defaultCurrency,
		));
	}


	/**
	 * Get the default timezone
	 */
	public function getDefaultTimezone() {
		return $this->_request('SitesManager.getDefaultTimezone');
	}

	/**
	 * Set the default timezone
	 *
	 * @param string $defaultTimezone
	 */
	public function setDefaultTimezone($defaultTimezone) {
		return $this->_request('SitesManager.setDefaultTimezone', array(
			'defaultTimezone' => $defaultTimezone,
		));
	}

	/**
	 * Update current site
	 *
	 * @param string $siteName
	 * @param array $urls
	 * @param boolean $ecommerce
	 * @param boolean $siteSearch
	 * @param string $searchKeywordParameters
	 * @param string $searchCategoryParameters
	 * @param array $excludeIps
	 * @param array $excludedQueryParameters
	 * @param string $timezone
	 * @param string $currency
	 * @param string $group
	 * @param string $startDate
	 * @param string $excludedUserAgents
	 * @param string $keepURLFragments
	 * @param string $type
	 */
	public function updateSite($siteName, $urls, $ecommerce = '', $siteSearch = '', $searchKeywordParameters = '',
		$searchCategoryParameters = '', $excludeIps = '', $excludedQueryParameters = '', $timezone = '', $currency = '',
		$group = '', $startDate = '', $excludedUserAgents = '', $keepURLFragments = '', $type = '') {
		return $this->_request('SitesManager.updateSite', array(
			'siteName' => $siteName,
			'urls' => $urls,
			'ecommerce' => $ecommerce,
			'siteSearch' => $siteSearch,
			'searchKeywordParameters' => $searchKeywordParameters,
			'searchCategoryParameters' => $searchCategoryParameters,
			'excludeIps' => $excludeIps,
			'excludedQueryParameters' => $excludedQueryParameters,
			'timezone' => $timezone,
			'currency' => $currency,
			'group' => $group,
			'startDate' => $startDate,
			'excludedUserAgents' => $excludedUserAgents,
			'keepURLFragments' => $keepURLFragments,
			'type' => $type,
		));
	}

	/**
	 * Get a list with all available currencies
	 */
	public function getCurrencyList() {
		return $this->_request('SitesManager.getCurrencyList');
	}

	/**
	 * Get a list with all currency symbols
	 */
	public function getCurrencySymbols() {
		return $this->_request('SitesManager.getCurrencySymbols');
	}

	/**
	 * Get a list with available timezones
	 */
	public function getTimezonesList() {
		return $this->_request('SitesManager.getTimezonesList');
	}

	/**
	 * Unknown
	 */
	public function getUniqueSiteTimezones() {
		return $this->_request('SitesManager.getUniqueSiteTimezones');
	}

	/**
	 * Rename group
	 *
	 * @param string $oldGroupName
	 * @param string $newGroupName
	 */
	public function renameGroup($oldGroupName, $newGroupName) {
		return $this->_request('SitesManager.renameGroup', array(
			'oldGroupName' => $oldGroupName,
			'newGroupName' => $newGroupName,
		));
	}

	/**
	 * Get all sites which matches the pattern
	 *
	 * @param string $pattern
	 */
	public function getPatternMatchSites($pattern) {
		return $this->_request('SitesManager.getPatternMatchSites', array(
			'pattern' => $pattern,
		));
	}

	/**
	 * MODULE: TRANSITIONS
	 * Get transitions for page URLs, titles and actions
	 */

	/**
	 * Get transitions for a page title
	 *
	 * @param $pageTitle
	 * @param string $segment
	 * @param string $limitBeforeGrouping
	 * @return mixed
	 */
	public function getTransitionsForPageTitle($pageTitle, $segment = '', $limitBeforeGrouping = '') {
		return $this->_request('Transitions.getTransitionsForPageTitle', array(
			'pageTitle' => $pageTitle,
			'segment' => $segment,
			'limitBeforeGrouping' => $limitBeforeGrouping,
		));
	}

	/**
	 * Get transitions for a page URL
	 *
	 * @param $pageUrl
	 * @param string $segment
	 * @param string $limitBeforeGrouping
	 * @return mixed
	 */
	public function getTransitionsForPageUrl($pageUrl, $segment = '', $limitBeforeGrouping = '') {
		return $this->_request('Transitions.getTransitionsForPageTitle', array(
			'pageUrl' => $pageUrl,
			'segment' => $segment,
			'limitBeforeGrouping' => $limitBeforeGrouping,
		));
	}

	/**
	 * Get transitions for a page URL
	 *
	 * @param $actionName
	 * @param $actionType
	 * @param string $segment
	 * @param string $limitBeforeGrouping
	 * @param string $parts
	 * @param bool $returnNormalizedUrls
	 * @return mixed
	 */
	public function getTransitionsForAction($actionName, $actionType, $segment = '', $limitBeforeGrouping = '', $parts = 'all', $returnNormalizedUrls = '') {
		return $this->_request('Transitions.getTransitionsForAction', array(
			'actionName' => $actionName,
			'actionType' => $actionType,
			'segment' => $segment,
			'limitBeforeGrouping' => $limitBeforeGrouping,
			'parts' => $parts,
			'returnNormalizedUrls' => $returnNormalizedUrls,
		));
	}

	/**
	 * Get translations for the transitions
	 *
	 * @return mixed
	 */
	public function getTransitionsTranslations() {
		return $this->_request('Transitions.getTranslations');
	}

	/**
	 * MODULE: USER COUNTRY
	 * Get visitors country information
	 */

	/**
	 * Get countries of all visitors
	 *
	 * @param string $segment
	 */
	public function getCountry($segment = '') {
		return $this->_request('UserCountry.getCountry', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get continents of all visitors
	 *
	 * @param string $segment
	 */
	public function getContinent($segment = '') {
		return $this->_request('UserCountry.getContinent', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get regions of all visitors
	 *
	 * @param string $segment
	 */
	public function getRegion($segment = '') {
		return $this->_request('UserCountry.getRegion', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get cities of all visitors
	 *
	 * @param string $segment
	 */
	public function getCity($segment = '') {
		return $this->_request('UserCountry.getCity', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get location from ip
	 *
	 * @param string $ip
	 * @param string $provider
	 */
	public function getLocationFromIP($ip, $provider = '') {
		return $this->_request('UserCountry.getLocationFromIP', array(
			'ip' => $ip,
			'provider' => $provider,
		));
	}

	/**
	 * Get the number of disting countries
	 *
	 * @param string $segment
	 */
	public function getCountryNumber($segment = '') {
		return $this->_request('UserCountry.getNumberOfDistinctCountries', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: USER SETTINGS
	 * Get the user settings
	 */

	/**
	 * Get resolution
	 *
	 * @param string $segment
	 */
	public function getResolution($segment = '') {
		return $this->_request('UserSettings.getResolution', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get configuration
	 *
	 * @param string $segment
	 */
	public function getConfiguration($segment = '') {
		return $this->_request('UserSettings.getConfiguration', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get plugins
	 *
	 * @param string $segment
	 */
	public function getUserPlugin($segment = '') {
		return $this->_request('UserSettings.getPlugin', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get language
	 *
	 * @param string $segment
	 */
	public function getUserLanguage($segment = '') {
		return $this->_request('UserSettings.getLanguage', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get language code
	 *
	 * @param string $segment
	 */
	public function getUserLanguageCode($segment = '') {
		return $this->_request('UserSettings.getLanguageCode', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: USER MANAGER
	 * Manage piwik users
	 */

	/**
	 * Set user preference
	 *
	 * @param string $userLogin Username
	 * @param string $preferenceName
	 * @param string $preferenceValue
	 */
	public function setUserPreference($userLogin, $preferenceName, $preferenceValue) {
		return $this->_request('UsersManager.setUserPreference', array(
			'userLogin' => $userLogin,
			'preferenceName' => $preferenceName,
			'preferenceValue' => $preferenceValue,
		));
	}

	/**
	 * Get user preference
	 *
	 * @param string $userLogin Username
	 * @param string $preferenceName
	 */
	public function getUserPreference($userLogin, $preferenceName) {
		return $this->_request('UsersManager.getUserPreference', array(
			'userLogin' => $userLogin,
			'preferenceName' => $preferenceName,
		));
	}

	/**
	 * Get user by username
	 *
	 * @param array $userLogins Array with Usernames
	 */
	public function getUsers($userLogins = '') {
		return $this->_request('UsersManager.getUsers', array(
			'userLogins' => $userLogins,
		));
	}

	/**
	 * Get all user logins
	 */
	public function getUsersLogin() {
		return $this->_request('UsersManager.getUsersLogin');
	}

	/**
	 * Get sites by user access
	 *
	 * @param string $access
	 */
	public function getUsersSitesFromAccess($access) {
		return $this->_request('UsersManager.getUsersSitesFromAccess', array(
			'access' => $access,
		));
	}

	/**
	 * Get all users with access level from the current site
	 */
	public function getUsersAccess() {
		return $this->_request('UsersManager.getUsersAccessFromSite');
	}

	/**
	 * Get all users with access $access to the current site
	 *
	 * @param string $access
	 */
	public function getUsersWithSiteAccess($access) {
		return $this->_request('UsersManager.getUsersWithSiteAccess', array(
			'access' => $access,
		));
	}

	/**
	 * Get site access from the user $userLogin
	 *
	 * @param string $userLogin Username
	 */
	public function getSitesAccessFromUser($userLogin) {
		return $this->_request('UsersManager.getSitesAccessFromUser', array(
			'userLogin' => $userLogin,
		));
	}

	/**
	 * Get user by login
	 *
	 * @param string $userLogin Username
	 */
	public function getUser($userLogin) {
		return $this->_request('UsersManager.getUser', array(
			'userLogin' => $userLogin,
		));
	}

	/**
	 * Get user by email
	 *
	 * @param string $userEmail
	 */
	public function getUserByEmail($userEmail) {
		return $this->_request('UsersManager.getUserByEmail', array(
			'userEmail' => $userEmail,
		));
	}

	/**
	 * Add a user
	 *
	 * @param string $userLogin Username
	 * @param string $password Password in clear text
	 * @param string $email
	 * @param string $alias
	 */
	public function addUser($userLogin, $password, $email, $alias = '') {
		return $this->_request('UsersManager.addUser', array(
			'userLogin' => $userLogin,
			'password' => $password,
			'email' => $email,
			'alias' => $alias,
		));
	}

	/**
	 * Set super user access
	 *
	 * @param string $userLogin Username
	 * @param integer $hasSuperUserAccess
	 */
	public function setSuperUserAccess($userLogin, $hasSuperUserAccess) {
		return $this->_request('UsersManager.setSuperUserAccess', array(
			'userLogin' => $userLogin,
			'hasSuperUserAccess' => $hasSuperUserAccess,
		));
	}

	/**
	 * Check if user has super user access
	 */
	public function hasSuperUserAccess() {
		return $this->_request('UsersManager.hasSuperUserAccess');
	}

	/**
	 * Get a list of users with super user access
	 */
	public function getUsersHavingSuperUserAccess() {
		return $this->_request('UsersManager.getUsersHavingSuperUserAccess');
	}

	/**
	 * Update a user
	 *
	 * @param string $userLogin Username
	 * @param string $password Password in clear text
	 * @param string $email
	 * @param string $alias
	 */
	public function updateUser($userLogin, $password = '', $email = '', $alias = '') {
		return $this->_request('UsersManager.updateUser', array(
			'userLogin' => $userLogin,
			'password' => $password,
			'email' => $email,
			'alias' => $alias,
		));
	}

	/**
	 * Delete a user
	 *
	 * @param string $userLogin Username
	 */
	public function deleteUser($userLogin) {
		return $this->_request('UsersManager.deleteUser', array(
			'userLogin' => $userLogin,
		));
	}

	/**
	 * Checks if a user exist
	 *
	 * @param string $userLogin
	 */
	public function userExists($userLogin) {
		return $this->_request('UsersManager.userExists', array(
			'userLogin' => $userLogin,
		));
	}

	/**
	 * Checks if a user exist by email
	 *
	 * @param string $userEmail
	 */
	public function userEmailExists($userEmail) {
		return $this->_request('UsersManager.userEmailExists', array(
			'userEmail' => $userEmail,
		));
	}

	/**
	 * Grant access to multiple sites
	 *
	 * @param string $userLogin Username
	 * @param string $access
	 * @param array $idSites
	 */
	public function setUserAccess($userLogin, $access, $idSites) {
		return $this->_request('UsersManager.setUserAccess', array(
			'userLogin' => $userLogin,
			'access' => $access,
			'idSites' => $idSites,
		));
	}

	/**
	 * Get the token for a user
	 *
	 * @param string $userLogin Username
	 * @param string $md5Password Password in clear text
	 */
	public function getTokenAuth($userLogin, $md5Password) {
		return $this->_request('UsersManager.getTokenAuth', array(
			'userLogin' => $userLogin,
			'md5Password' => md5($md5Password),
		));
	}

	/**
	 * MODULE: VISIT FREQUENCY
	 * Get visit frequency
	 */

	/**
	 * Get the visit frequency
	 *
	 * @param string $segment
	 * @param string $columns
	 */
	public function getVisitFrequency($segment = '', $columns = '') {
		return $this->_request('VisitFrequency.get', array(
			'segment' => $segment,
			'columns' => $columns,
		));
	}

	/**
	 * MODULE: VISIT TIME
	 * Get visit time
	 */

	/**
	 * Get the visit by local time
	 *
	 * @param string $segment
	 */
	public function getVisitLocalTime($segment = '') {
		return $this->_request('VisitTime.getVisitInformationPerLocalTime', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the visit by server time
	 *
	 * @param string $segment
	 * @param boolean $hideFutureHoursWhenToday Hide the future hours when the report is created for today
	 */
	public function getVisitServerTime($segment = '', $hideFutureHoursWhenToday = '') {
		return $this->_request('VisitTime.getVisitInformationPerServerTime', array(
			'segment' => $segment,
			'hideFutureHoursWhenToday' => $hideFutureHoursWhenToday,
		));
	}

	/**
	 * Get the visit by server time
	 *
	 * @param string $segment
	 */
	public function getByDayOfWeek($segment = '') {
		return $this->_request('VisitTime.getByDayOfWeek', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: VISITOR INTEREST
	 * Get the interests of the visitor
	 */

	/**
	 * Get the number of visits per visit duration
	 *
	 * @param string $segment
	 */
	public function getNumberOfVisitsPerDuration($segment = '') {
		return $this->_request('VisitorInterest.getNumberOfVisitsPerVisitDuration', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of visits per visited page
	 *
	 * @param string $segment
	 */
	public function getNumberOfVisitsPerPage($segment = '') {
		return $this->_request('VisitorInterest.getNumberOfVisitsPerPage', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of days elapsed since the last visit
	 *
	 * @param string $segment
	 */
	public function getNumberOfVisitsByDaySinceLast($segment = '') {
		return $this->_request('VisitorInterest.getNumberOfVisitsByDaysSinceLast', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the number of visits by visit count
	 *
	 * @param string $segment
	 */
	public function getNumberOfVisitsByCount($segment = '') {
		return $this->_request('VisitorInterest.getNumberOfVisitsByVisitCount', array(
			'segment' => $segment,
		));
	}

	/**
	 * MODULE: VISITS SUMMARY
	 * Get visit summary information
	 */

	/**
	 * Get a visit summary
	 *
	 * @param string $segment
	 * @param string $columns
	 */
	public function getVisitsSummary($segment = '', $columns = '') {
		return $this->_request('VisitsSummary.get', array(
			'segment' => $segment,
			'columns' => $columns,
		));
	}

	/**
	 * Get visits
	 *
	 * @param string $segment
	 */
	public function getVisits($segment = '') {
		return $this->_request('VisitsSummary.getVisits', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get unique visits
	 *
	 * @param string $segment
	 */
	public function getUniqueVisitors($segment = '') {
		return $this->_request('VisitsSummary.getUniqueVisitors', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get user visit summary
	 *
	 * @param string $segment
	 */
	public function getUserVisitors($segment = '') {
		return $this->_request('VisitsSummary.getUsers', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get actions
	 *
	 * @param string $segment
	 */
	public function getActions($segment = '') {
		return $this->_request('VisitsSummary.getActions', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get max actions
	 *
	 * @param string $segment
	 */
	public function getMaxActions($segment = '') {
		return $this->_request('VisitsSummary.getMaxActions', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get bounce count
	 *
	 * @param string $segment
	 */
	public function getBounceCount($segment = '') {
		return $this->_request('VisitsSummary.getBounceCount', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get converted visits
	 *
	 * @param string $segment
	 */
	public function getVisitsConverted($segment = '') {
		return $this->_request('VisitsSummary.getVisitsConverted', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the sum of all visit lengths
	 *
	 * @param string $segment
	 */
	public function getSumVisitsLength($segment = '') {
		return $this->_request('VisitsSummary.getSumVisitsLength', array(
			'segment' => $segment,
		));
	}

	/**
	 * Get the sum of all visit lengths formated in the current language
	 *
	 * @param string $segment
	 */
	public function getSumVisitsLengthPretty($segment = '') {
		return $this->_request('VisitsSummary.getSumVisitsLengthPretty', array(
			'segment' => $segment,
		));
	}
}
