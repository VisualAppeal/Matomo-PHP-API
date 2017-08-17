<?php namespace VisualAppeal;

use Httpful\Request;

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
    private $_isJsonDecodeAssoc = false;

    private $_limit = '';

    private $_errors = [];

    public $verifySsl = false;

    public $maxRedirects = 5;

    /**
     * @deprecated
     */
    public $redirects = 0;

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
    function __construct(
        $site,
        $token,
        $siteId,
        $format = self::FORMAT_JSON,
        $period = self::PERIOD_DAY,
        $date = self::DATE_YESTERDAY,
        $rangeStart = '',
        $rangeEnd = null
    ) {
        $this->_site = $site;
        $this->_token = $token;
        $this->_siteId = $siteId;
        $this->_format = $format;
        $this->_period = $period;
        $this->_rangeStart = $rangeStart;
        $this->_rangeEnd = $rangeEnd;

        if (!empty($rangeStart)) {
            $this->setRange($rangeStart, $rangeEnd);
        } else {
            $this->setDate($date);
        }
    }

    /**
     * Getter & Setter
     */

    /**
     * Get the url of the piwik installation
     *
     * @return string
     */
    public function getSite()
    {
        return $this->_site;
    }

    /**
     * Set the URL of the piwik installation
     *
     * @param string $url
     * @return $this
     */
    public function setSite($url)
    {
        $this->_site = $url;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->_token = $token;

        return $this;
    }

    /**
     * Get current site ID
     *
     * @return int
     */
    public function getSiteId()
    {
        return intval($this->_siteId);
    }

    /**
     * Set current site ID
     *
     * @param int $id
     * @return $this
     */
    public function setSiteId($id)
    {
        $this->_siteId = $id;

        return $this;
    }

    /**
     * Get response format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Set response format
     *
     * @param string $format
     *        FORMAT_XML
     *        FORMAT_JSON
     *        FORMAT_CSV
     *        FORMAT_TSV
     *        FORMAT_HTML
     *        FORMAT_RSS
     *        FORMAT_PHP
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->_language = $language;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Set date
     *
     * @param string $date Format Y-m-d or class constant:
     *        DATE_TODAY
     *        DATE_YESTERDAY
     * @return $this
     */
    public function setDate($date)
    {
        $this->_date = $date;
        $this->_rangeStart = null;
        $this->_rangeEnd = null;

        return $this;
    }

    /**
     * Get  period
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->_period;
    }

    /**
     * Set time period
     *
     * @param string $period
     *        PERIOD_DAY
     *        PERIOD_MONTH
     *        PERIOD_WEEK
     *        PERIOD_YEAR
     *        PERIOD_RANGE
     * @return $this
     */
    public function setPeriod($period)
    {
        $this->_period = $period;

        return $this;
    }

    /**
     * Get the date range comma separated
     *
     * @return string
     */
    public function getRange()
    {
        if (empty($this->_rangeEnd)) {
            return $this->_rangeStart;
        } else {
            return $this->_rangeStart . ',' . $this->_rangeEnd;
        }
    }

    /**
     * Set date range
     *
     * @param string $rangeStart e.g. 2012-02-10 (YYYY-mm-dd) or last5(lastX), previous12(previousY)...
     * @param string $rangeEnd e.g. 2012-02-12. Leave this parameter empty to request all data from
     *                         $rangeStart until now
     * @return $this
     */
    public function setRange($rangeStart, $rangeEnd = null)
    {
        $this->_date = '';
        $this->_rangeStart = $rangeStart;
        $this->_rangeEnd = $rangeEnd;

        if (is_null($rangeEnd)) {
            if (strpos($rangeStart, 'last') !== false || strpos($rangeStart, 'previous') !== false) {
                $this->setDate($rangeStart);
            } else {
                $this->_rangeEnd = self::DATE_TODAY;
            }
        }

        return $this;
    }

    /**
     * Get the limit of returned rows
     *
     * @return int
     */
    public function getLimit()
    {
        return intval($this->_limit);
    }

    /**
     * Set the limit of returned rows
     *
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;

        return $this;
    }

    /**
     * Sets the json_decode format
     *
     * @param bool $isJsonDecodeAssoc false decode as Object, true for decode as Associate array
     */
    public function setIsJsonDecodeAssoc($isJsonDecodeAssoc)
    {
        $this->_isJsonDecodeAssoc = $isJsonDecodeAssoc;
    }

    /**
     * Return if JSON decode an associate array
     *
     * @return bool
     */
    public function isIsJsonDecodeAssoc()
    {
        return $this->_isJsonDecodeAssoc;
    }

    /**
     * Reset all default variables
     */
    public function reset()
    {
        $this->_period = self::PERIOD_DAY;
        $this->_date = '';
        $this->_rangeStart = 'yesterday';
        $this->_rangeEnd = null;

        $this->_errors = [];

        return $this;
    }

    /**
     * Requests to Piwik api
     */

    /**
     * Make API request
     *
     * @param string $method
     * @param array $params
     * @param array $optional
     * @return bool|object
     */
    private function _request($method, $params = [], $optional = [])
    {
        $url = $this->_parseUrl($method, $params + $optional);
        if ($url === false) {
            return false;
        }

        $req = Request::get($url);
        $req->strict_ssl = $this->verifySsl;
        $req->max_redirects = $this->maxRedirects;
        $req->setConnectionTimeout(5);

        $buffer = $req->send();

        if (!empty($buffer)) {
            $request = $this->_parseRequest($buffer);
        } else {
            $request = false;
        }

        return $this->_finishRequest($request, $method, $params + $optional);
    }

    /**
     * Validate request and return the values
     *
     * @param object $request
     * @param string $method
     * @param array $params
     * @return bool|object
     */
    private function _finishRequest($request, $method, $params)
    {
        $valid = $this->_validRequest($request);

        if ($valid === true) {
            if (isset($request->value)) {
                return $request->value;
            } else {
                return $request;
            }
        } else {
            $this->_addError($valid . ' (' . $this->_parseUrl($method, $params) . ')');
            return false;
        }
    }

    /**
     * Create request url with parameters
     *
     * @param string $method The request method
     * @param array $params Request params
     * @return string
     */
    private function _parseUrl($method, array $params = [])
    {
        $params = [
            'module' => 'API',
            'method' => $method,
            'token_auth' => $this->_token,
            'idSite' => $this->_siteId,
            'period' => $this->_period,
            'format' => $this->_format,
            'language' => $this->_language
        ] + $params;

        foreach ($params as $key => $value) {
            $params[$key] = urlencode($value);
        }

        if (!empty($this->_rangeStart) && !empty($this->_rangeEnd)) {
            $params = $params + [
                'date' => $this->_rangeStart . ',' . $this->_rangeEnd,
            ];
        } elseif (!empty($this->_date)) {
            $params = $params + [
                'date' => $this->_date,
            ];
        } else {
            $this->_addError('Specify a date or a date range!');
            return false;
        }

        $url = $this->_site;

        $i = 0;
        foreach ($params as $param => $val) {
            if (!empty($val)) {
                $i++;
                if ($i > 1) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }

                if (is_array($val)) {
                    $val = implode(',', $val);
                }
                $url .= $param . '=' . $val;
            }
        }

        return $url;
    }

    /**
     * Validate the request result
     *
     * @param object $request
     * @return bool|int
     */
    private function _validRequest($request)
    {
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
     * @param object $request
     * @return mixed|object
     */
    private function _parseRequest($request)
    {
        switch ($this->_format) {
            case self::FORMAT_JSON:
                return json_decode($request, $this->_isJsonDecodeAssoc);
                break;
            default:
                return $request;
        }
    }

    /**
     * Error methods
     */

    /**
     * Add error
     *
     * @param string $msg Error message
     */
    protected function _addError($msg = '')
    {
        array_push($this->_errors, $msg);
    }

    /**
     * Check for errors
     */
    public function hasError()
    {
        return (count($this->_errors) > 0);
    }

    /**
     * Return all errors
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * MODULE: API
     * API metadata
     */

    /**
     * Get current piwik version
     *
     * @param array $optional
     * @return bool|object
     */
    public function getPiwikVersion($optional = [])
    {
        return $this->_request('API.getPiwikVersion', [], $optional);
    }

    /**
     * Get current ip address (from the server executing this script)
     *
     * @param array $optional
     * @return bool|object
     */
    public function getIpFromHeader($optional = [])
    {
        return $this->_request('API.getIpFromHeader', [], $optional);
    }

    /**
     * Get current settings
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSettings($optional = [])
    {
        return $this->_request('API.getSettings', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultMetricTranslations($optional = [])
    {
        return $this->_request('API.getDefaultMetricTranslations', [], $optional);
    }

    /**
     * Get default metrics
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultMetrics($optional = [])
    {
        return $this->_request('API.getDefaultMetrics', [], $optional);
    }

    /**
     * Get default processed metrics
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultProcessedMetrics($optional = [])
    {
        return $this->_request('API.getDefaultProcessedMetrics', [], $optional);
    }

    /**
     * Get default metrics documentation
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultMetricsDocumentation($optional = [])
    {
        return $this->_request('API.getDefaultMetricsDocumentation', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param array $sites Array with the ID's of the sites
     * @param array $optional
     * @return bool|object
     */
    public function getSegmentsMetadata($sites = [], $optional = [])
    {
        return $this->_request('API.getSegmentsMetadata', [
            'idSites' => $sites
        ], $optional);
    }

    /**
     * Get the url of the logo
     *
     * @param bool $pathOnly Return the url (false) or the absolute path (true)
     * @param array $optional
     * @return bool|object
     */
    public function getLogoUrl($pathOnly = false, $optional = [])
    {
        return $this->_request('API.getLogoUrl', [
            'pathOnly' => $pathOnly
        ], $optional);
    }

    /**
     * Get the url of the header logo
     *
     * @param bool $pathOnly Return the url (false) or the absolute path (true)
     * @param array $optional
     * @return bool|object
     */
    public function getHeaderLogoUrl($pathOnly = false, $optional = [])
    {
        return $this->_request('API.getHeaderLogoUrl', [
            'pathOnly' => $pathOnly
        ], $optional);
    }

    /**
     * Get metadata from the API
     *
     * @param string $apiModule Module
     * @param string $apiAction Action
     * @param array $apiParameters Parameters
     * @param array $optional
     * @return bool|object
     */
    public function getMetadata($apiModule, $apiAction, $apiParameters = [], $optional = [])
    {
        return $this->_request('API.getMetadata', [
            'apiModule' => $apiModule,
            'apiAction' => $apiAction,
            'apiParameters' => $apiParameters,
        ], $optional);
    }

    /**
     * Get metadata from a report
     *
     * @param array $idSites Array with the ID's of the sites
     * @param string $hideMetricsDoc
     * @param string $showSubtableReports
     * @param array $optional
     * @return bool|object
     */
    public function getReportMetadata(
        array $idSites,
        $hideMetricsDoc = '',
        $showSubtableReports = '',
        $optional = []
    ) {
        return $this->_request('API.getReportMetadata', [
            'idSites' => $idSites,
            'hideMetricsDoc' => $hideMetricsDoc,
            'showSubtableReports' => $showSubtableReports,
        ], $optional);
    }

    /**
     * Get processed report
     *
     * @param string $apiModule Module
     * @param string $apiAction Action
     * @param string $segment
     * @param string $apiParameters
     * @param int|string $idGoal
     * @param bool|string $showTimer
     * @param string $hideMetricsDoc
     * @param array $optional
     * @return bool|object
     */
    public function getProcessedReport(
        $apiModule,
        $apiAction,
        $segment = '',
        $apiParameters = '',
        $idGoal = '',
        $showTimer = '1',
        $hideMetricsDoc = '',
        $optional = []
    ) {
        return $this->_request('API.getProcessedReport', [
            'apiModule' => $apiModule,
            'apiAction' => $apiAction,
            'segment' => $segment,
            'apiParameters' => $apiParameters,
            'idGoal' => $idGoal,
            'showTimer' => $showTimer,
            'hideMetricsDoc' => $hideMetricsDoc,
        ], $optional);
    }

    /**
     * Get Api
     *
     * @param string $segment
     * @param string $columns
     * @param array $optional
     * @return bool|object
     */
    public function getApi($segment = '', $columns = '', $optional = [])
    {
        return $this->_request('API.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get row evolution
     *
     * @param $apiModule
     * @param $apiAction
     * @param string $segment
     * @param $column
     * @param string $idGoal
     * @param string $legendAppendMetric
     * @param string $labelUseAbsoluteUrl
     * @param array $optional
     * @return bool|object
     */
    public function getRowEvolution(
        $apiModule,
        $apiAction,
        $segment = '',
        $column = '',
        $idGoal = '',
        $legendAppendMetric = '1',
        $labelUseAbsoluteUrl = '1',
        $optional = []
    ) {
        return $this->_request('API.getRowEvolution', [
            'apiModule' => $apiModule,
            'apiAction' => $apiAction,
            'segment' => $segment,
            'column' => $column,
            'idGoal' => $idGoal,
            'legendAppendMetric' => $legendAppendMetric,
            'labelUseAbsoluteUrl' => $labelUseAbsoluteUrl,
        ], $optional);
    }

    /**
     * Get last date
     *
     * @param array $optional
     *
     * @deprecated 2.15.0 https://developer.piwik.org/changelog#piwik-2150
     * @return bool|object
     */
    public function getLastDate($optional = [])
    {
        return $this->_request('API.getLastDate', [], $optional);
    }

    /**
     * Get the result of multiple requests bundled together
     * Take as an argument an array of the API methods to send together
     * For example, ['API.get', 'Action.get', 'DeviceDetection.getType']
     *
     * @param array $methods
     * @param array $optional
     * @return bool|object
     */
    public function getBulkRequest($methods = [], $optional = [])
    {
        $urls = [];

        foreach ($methods as $key => $method) {
            $urls['urls[' . $key . ']'] = urlencode('method=' . $method);
        }

        return $this->_request('API.getBulkRequest', $urls, $optional);
    }

    /**
     * Get suggested values for segments
     *
     * @param string $segmentName
     * @param array $optional
     * @return bool|object
     */
    public function getSuggestedValuesForSegment($segmentName, $optional = [])
    {
        return $this->_request('API.getSuggestedValuesForSegment', [
            'segmentName' => $segmentName,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getAction($segment = '', $columns = '', $optional = [])
    {
        return $this->_request('Actions.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get page urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageUrls($segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page URLs after a site search
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageUrlsFollowingSiteSearch($segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageUrlsFollowingSiteSearch', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles after a site search
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageTitlesFollowingSiteSearch($segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageTitlesFollowingSiteSearch', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get entry page urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getEntryPageUrls($segment = '', $optional = [])
    {
        return $this->_request('Actions.getEntryPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get exit page urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getExitPageUrls($segment = '', $optional = [])
    {
        return $this->_request('Actions.getExitPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page url information
     *
     * @param string $pageUrl The page url
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageUrl($pageUrl, $segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageUrl', [
            'pageUrl' => $pageUrl,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageTitles($segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get entry page urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getEntryPageTitles($segment = '', $optional = [])
    {
        return $this->_request('Actions.getEntryPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get exit page urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getExitPageTitles($segment = '', $optional = [])
    {
        return $this->_request('Actions.getExitPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles
     *
     * @param string $pageName The page name
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getPageTitle($pageName, $segment = '', $optional = [])
    {
        return $this->_request('Actions.getPageTitle', [
            'pageName' => $pageName,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get downloads
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getDownloads($segment = '', $optional = [])
    {
        return $this->_request('Actions.getDownloads', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get download information
     *
     * @param string $downloadUrl URL of the download
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getDownload($downloadUrl, $segment = '', $optional = [])
    {
        return $this->_request('Actions.getDownload', [
            'downloadUrl' => $downloadUrl,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get outlinks
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getOutlinks($segment = '', $optional = [])
    {
        return $this->_request('Actions.getOutlinks', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get outlink information
     *
     * @param string $outlinkUrl URL of the outlink
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getOutlink($outlinkUrl, $segment = '', $optional = [])
    {
        return $this->_request('Actions.getOutlink', [
            'outlinkUrl' => $outlinkUrl,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the site search keywords
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSiteSearchKeywords($segment = '', $optional = [])
    {
        return $this->_request('Actions.getSiteSearchKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get search keywords with no search results
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSiteSearchNoResultKeywords($segment = '', $optional = [])
    {
        return $this->_request('Actions.getSiteSearchNoResultKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get site search categories
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSiteSearchCategories($segment = '', $optional = [])
    {
        return $this->_request('Actions.getSiteSearchCategories', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: ANNOTATIONS
     */

    /**
     * Add annotation
     *
     * @param string $note
     * @param int $starred
     * @param array $optional
     * @return bool|object
     */
    public function addAnnotation($note, $starred = 0, $optional = [])
    {
        return $this->_request('Annotations.add', [
            'note' => $note,
            'starred' => $starred,
        ], $optional);
    }

    /**
     * Save annotation
     *
     * @param int $idNote
     * @param string $note
     * @param string $starred
     * @param array $optional
     * @return bool|object
     */
    public function saveAnnotation($idNote, $note = '', $starred = '', $optional = [])
    {
        return $this->_request('Annotations.save', [
            'idNote' => $idNote,
            'note' => $note,
            'starred' => $starred,
        ], $optional);
    }

    /**
     * Delete annotation
     *
     * @param int $idNote
     * @param array $optional
     * @return bool|object
     */
    public function deleteAnnotation($idNote, $optional = [])
    {
        return $this->_request('Annotations.delete', [
            'idNote' => $idNote,
        ], $optional);
    }

    /**
     * Delete all annotations
     *
     * @param array $optional
     * @return bool|object
     */
    public function deleteAllAnnotations($optional = [])
    {
        return $this->_request('Annotations.deleteAll', [], $optional);
    }

    /**
     * Get annotation
     *
     * @param int $idNote
     * @param array $optional
     * @return bool|object
     */
    public function getAnnotation($idNote, $optional = [])
    {
        return $this->_request('Annotations.get', [
            'idNote' => $idNote,
        ], $optional);
    }

    /**
     * Get all annotations
     *
     * @param string $lastN
     * @param array $optional
     * @return bool|object
     */
    public function getAllAnnotation($lastN = '', $optional = [])
    {
        return $this->_request('Annotations.getAll', [
            'lastN' => $lastN,
        ], $optional);
    }

    /**
     * Get number of annotation for current period
     *
     * @param int $lastN
     * @param string $getAnnotationText
     * @param array $optional
     * @return bool|object
     */
    public function getAnnotationCountForDates($lastN, $getAnnotationText, $optional = [])
    {
        return $this->_request('Annotations.getAnnotationCountForDates', [
            'lastN' => $lastN,
            'getAnnotationText' => $getAnnotationText
        ], $optional);
    }

    /**
     * MODULE: CONTENTS
     */

    /**
     * Get content names
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getContentNames($segment = '', $optional = [])
    {
        return $this->_request('Contents.getContentNames', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get content pieces
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getContentPieces($segment = '', $optional = [])
    {
        return $this->_request('Contents.getContentPieces', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: CUSTOM ALERTS
     */

    /**
     * Get alert details
     *
     * @param int $idAlert
     * @param array $optional
     * @return bool|object
     */
    public function getAlert($idAlert, $optional = [])
    {
        return $this->_request('CustomAlerts.getAlert', [
            'idAlert' => $idAlert,
        ], $optional);
    }

    /**
     * Get values for alerts in the past
     *
     * @param int $idAlert
     * @param string $subPeriodN
     * @param array $optional
     * @return bool|object
     */
    public function getValuesForAlertInPast($idAlert, $subPeriodN, $optional = [])
    {
        return $this->_request('CustomAlerts.getValuesForAlertInPast', [
            'idAlert' => $idAlert,
            'subPeriodN' => $subPeriodN,
        ], $optional);
    }

    /**
     * Get all alert details
     *
     * @param string $idSites Comma separated list of site IDs
     * @param string $ifSuperUserReturnAllAlerts
     * @param array $optional
     * @return bool|object
     */
    public function getAlerts($idSites, $ifSuperUserReturnAllAlerts = '', $optional = [])
    {
        return $this->_request('CustomAlerts.getAlerts', [
            'idSites' => $idSites,
            'ifSuperUserReturnAllAlerts' => $ifSuperUserReturnAllAlerts,
        ], $optional);
    }

    /**
     * Add alert
     *
     * @param string $name
     * @param array $idSites Array of site IDs
     * @param int $emailMe
     * @param string $additionalEmails
     * @param string $phoneNumbers
     * @param string $metric
     * @param string $metricCondition
     * @param string $metricValue
     * @param string $comparedTo
     * @param string $reportUniqueId
     * @param string $reportCondition
     * @param string $reportValue
     * @param array $optional
     * @return bool|object
     */
    public function addAlert(
        $name,
        $idSites,
        $emailMe,
        $additionalEmails,
        $phoneNumbers,
        $metric,
        $metricCondition,
        $metricValue,
        $comparedTo,
        $reportUniqueId,
        $reportCondition = '',
        $reportValue = '',
        $optional = []
    ) {
        return $this->_request('CustomAlerts.addAlert', [
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
        ], $optional);
    }

    /**
     * Edit alert
     *
     * @param int $idAlert
     * @param string $name
     * @param array $idSites Array of site IDs
     * @param int $emailMe
     * @param string $additionalEmails
     * @param string $phoneNumbers
     * @param string $metric
     * @param string $metricCondition
     * @param string $metricValue
     * @param string $comparedTo
     * @param string $reportUniqueId
     * @param string $reportCondition
     * @param string $reportValue
     * @param array $optional
     * @return bool|object
     */
    public function editAlert(
        $idAlert,
        $name,
        $idSites,
        $emailMe,
        $additionalEmails,
        $phoneNumbers,
        $metric,
        $metricCondition,
        $metricValue,
        $comparedTo,
        $reportUniqueId,
        $reportCondition = '',
        $reportValue = '',
        $optional = []
    ) {
        return $this->_request('CustomAlerts.editAlert', [
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
        ], $optional);
    }

    /**
     * Delete Alert
     *
     * @param int $idAlert
     * @param array $optional
     * @return bool|object
     */
    public function deleteAlert($idAlert, $optional = [])
    {
        return $this->_request('CustomAlerts.deleteAlert', [
            'idAlert' => $idAlert,
        ], $optional);
    }

    /**
     * Get triggered alerts
     *
     * @param array $idSites
     * @param array $optional
     * @return bool|object
     */
    public function getTriggeredAlerts($idSites, $optional = [])
    {
        return $this->_request('CustomAlerts.getTriggeredAlerts', [
            'idSites' => $idSites,
        ], $optional);
    }

    /**
     * MODULE: Custom Dimensions
     * The Custom Dimensions API lets you manage and access reports for your configured Custom Dimensions.
     */

    /**
     * Fetch a report for the given idDimension. Only reports for active dimensions can be fetched. Requires at least
     * view access.
     *
     * @param int $idDimension
     * @param array $optional
     *
     * @return bool|object
     */
    public function getCustomDimension($idDimension, $optional = [])
    {
        return $this->_request('CustomDimensions.getCustomDimension', [
            'idDimension' => $idDimension,
        ], $optional);
    }

    /**
     * Configures a new Custom Dimension. Note that Custom Dimensions cannot be deleted, be careful when creating one
     * as you might run quickly out of available Custom Dimension slots. Requires at least Admin access for the
     * specified website. A current list of available `$scopes` can be fetched via the API method
     * `CustomDimensions.getAvailableScopes()`. This method will also contain information whether actually Custom
     * Dimension slots are available or whether they are all already in use.
     *
     * @param string $name The name of the dimension
     * @param string $scope Either 'visit' or 'action'. To get an up to date list of available scopes fetch the
     *                      API method `CustomDimensions.getAvailableScopes`
     * @param int $active  '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $optional
     *
     * @return bool|object
     */
    public function configureNewCustomDimension($name, $scope, $active, $optional = [])
    {
        return $this->_request('CustomDimensions.configureNewCustomDimension', [
            'name' => $name,
            'scope' => $scope,
            'active' => $active,
        ], $optional);
    }

    /**
     * Updates an existing Custom Dimension. This method updates all values, you need to pass existing values of the
     * dimension if you do not want to reset any value. Requires at least Admin access for the specified website.
     *
     * @param int $idDimension  The id of a Custom Dimension.
     * @param string $name      The name of the dimension
     * @param int $active       '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $optional
     *
     * @return bool|object
     */
    public function configureExistingCustomDimension($idDimension, $name, $active, $optional = [])
    {
        return $this->_request('CustomDimensions.configureExistingCustomDimension', [
            'idDimension' => $idDimension,
            'name' => $name,
            'active' => $active,
        ], $optional);
    }

    /**
     * @return bool|object
     */
    public function getConfiguredCustomDimensions()
    {
        return $this->_request('CustomDimensions.getConfiguredCustomDimensions', [
        ]);
    }

    /**
     * Get a list of all supported scopes that can be used in the API method
     * `CustomDimensions.configureNewCustomDimension`. The response also contains information whether more Custom
     * Dimensions can be created or not. Requires at least Admin access for the specified website.
     *
     * @return bool|object
     */
    public function getAvailableScopes()
    {
        return $this->_request('CustomDimensions.getAvailableScopes', [
        ]);
    }

    /**
     * Get a list of all available dimensions that can be used in an extraction. Requires at least Admin access
     * to one website.
     *
     * @return bool|object
     */
    public function getAvailableExtractionDimensions()
    {
        return $this->_request('CustomDimensions.getAvailableExtractionDimensions', [
        ]);
    }

    /**
     * MODULE: CUSTOM VARIABLES
     * Custom variable information
     */

    /**
     * Get custom variables
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCustomVariables($segment = '', $optional = [])
    {
        return $this->_request('CustomVariables.getCustomVariables', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get information about a custom variable
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCustomVariable($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('CustomVariables.getCustomVariablesValuesFromNameId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: Dashboard
     */

    /**
     * Get list of dashboards
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDashboards($optional = [])
    {
        return $this->_request('Dashboard.getDashboards', [], $optional);
    }

    /**
     * MODULE: DEVICES DETECTION
     */

    /**
     * Get Device Type.
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getDeviceType($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getType', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get Device Brand.
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getDeviceBrand($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getBrand', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get Device Model.
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getDeviceModel($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getModel', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get operating system families
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getOSFamilies($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getOsFamilies', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get os versions
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getOsVersions($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getOsVersions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browsers
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getBrowsers($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getBrowsers', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browser versions
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getBrowserVersions($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getBrowserVersions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browser engines
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getBrowserEngines($segment = '', $optional = [])
    {
        return $this->_request('DevicesDetection.getBrowserEngines', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: EVENTS
     */

    /**
     * Get event categories
     *
     * @param string $segment
     * @param string $secondaryDimension ('eventAction' or 'eventName')
     * @param array $optional
     * @return bool|object
     */
    public function getEventCategory($segment = '', $secondaryDimension = '', $optional = [])
    {
        return $this->_request('Events.getCategory', [
            'segment' => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get event actions
     *
     * @param string $segment
     * @param string $secondaryDimension ('eventName' or 'eventCategory')
     * @param array $optional
     * @return bool|object
     */
    public function getEventAction($segment = '', $secondaryDimension = '', $optional = [])
    {
        return $this->_request('Events.getAction', [
            'segment' => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get event names
     *
     * @param string $segment
     * @param string $secondaryDimension ('eventAction' or 'eventCategory')
     * @param array $optional
     * @return bool|object
     */
    public function getEventName($segment = '', $secondaryDimension = '', $optional = [])
    {
        return $this->_request('Events.getName', [
            'segment' => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get action from category ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getActionFromCategoryId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getActionFromCategoryId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get name from category ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNameFromCategoryId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getNameFromCategoryId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get category from action ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCategoryFromActionId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getCategoryFromActionId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get name from action ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNameFromActionId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getNameFromActionId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get action from name ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getActionFromNameId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getActionFromNameId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get category from name ID
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCategoryFromNameId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Events.getCategoryFromNameId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: EXAMPLE API
     * Get api and piwiki information
     */

    /**
     * Get the piwik version
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExamplePiwikVersion($optional = [])
    {
        return $this->_request('ExampleAPI.getPiwikVersion', [], $optional);
    }

    /**
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleAnswerToLife($optional = [])
    {
        return $this->_request('ExampleAPI.getAnswerToLife', [], $optional);
    }

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleObject($optional = [])
    {
        return $this->_request('ExampleAPI.getObject', [], $optional);
    }

    /**
     * Get the sum of the parameters
     *
     * @param int $a
     * @param int $b
     * @param array $optional
     * @return bool|object
     */
    public function getExampleSum($a = 0, $b = 0, $optional = [])
    {
        return $this->_request('ExampleAPI.getSum', [
            'a' => $a,
            'b' => $b,
        ], $optional);
    }

    /**
     * Returns nothing but the success of the request
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleNull($optional = [])
    {
        return $this->_request('ExampleAPI.getNull', [], $optional);
    }

    /**
     * Get a short piwik description
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleDescriptionArray($optional = [])
    {
        return $this->_request('ExampleAPI.getDescriptionArray', [], $optional);
    }

    /**
     * Get a short comparison with other analytic software
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleCompetitionDatatable($optional = [])
    {
        return $this->_request('ExampleAPI.getCompetitionDatatable', [], $optional);
    }

    /**
     * Get information about 42
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleMoreInformationAnswerToLife($optional = [])
    {
        return $this->_request('ExampleAPI.getMoreInformationAnswerToLife', [], $optional);
    }

    /**
     * Get a multidimensional array
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExampleMultiArray($optional = [])
    {
        return $this->_request('ExampleAPI.getMultiArray', [], $optional);
    }

    /**
     * MODULE: EXAMPLE PLUGIN
     */

    /**
     * Get a multidimensional array
     *
     * @param int $truth
     * @param array $optional
     * @return bool|object
     */
    public function getExamplePluginAnswerToLife($truth = 1, $optional = [])
    {
        return $this->_request('ExamplePlugin.getAnswerToLife', [
            'truth' => $truth,
        ], $optional);
    }

    /**
     * Get a multidimensional array
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getExamplePluginReport($segment = '', $optional = [])
    {
        return $this->_request('ExamplePlugin.getExampleReport', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: FEEDBACK
     */

    /**
     * Get a multidimensional array
     *
     * @param string $featureName
     * @param string $like
     * @param string $message
     * @param array $optional
     * @return bool|object
     */
    public function sendFeedbackForFeature($featureName, $like, $message = '', $optional = [])
    {
        return $this->_request('Feedback.sendFeedbackForFeature', [
            'featureName' => $featureName,
            'like' => $like,
            'message' => $message,
        ], $optional);
    }

    /**
     * MODULE: GOALS
     * Handle goals
     */

    /**
     * Get all goals
     *
     * @param array $optional
     * @return bool|object
     */
    public function getGoals($optional = [])
    {
        return $this->_request('Goals.getGoals', [], $optional);
    }

    /**
     * Add a goal
     *
     * @param string $name
     * @param string $matchAttribute
     * @param string $pattern
     * @param string $patternType
     * @param string $caseSensitive
     * @param string $revenue
     * @param string $allowMultipleConversionsPerVisit
     * @param array $optional
     * @return bool|object
     */
    public function addGoal(
        $name,
        $matchAttribute,
        $pattern,
        $patternType,
        $caseSensitive = '',
        $revenue = '',
        $allowMultipleConversionsPerVisit = '',
        $optional = []
    ) {
        return $this->_request('Goals.addGoal', [
            'name' => $name,
            'matchAttribute' => $matchAttribute,
            'pattern' => $pattern,
            'patternType' => $patternType,
            'caseSensitive' => $caseSensitive,
            'revenue' => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ], $optional);
    }

    /**
     * Update a goal
     *
     * @param int $idGoal
     * @param string $name
     * @param string $matchAttribute
     * @param string $pattern
     * @param string $patternType
     * @param string $caseSensitive
     * @param string $revenue
     * @param string $allowMultipleConversionsPerVisit
     * @param array $optional
     * @return bool|object
     */
    public function updateGoal(
        $idGoal,
        $name,
        $matchAttribute,
        $pattern,
        $patternType,
        $caseSensitive = '',
        $revenue = '',
        $allowMultipleConversionsPerVisit = '',
        $optional = []
    ) {
        return $this->_request('Goals.updateGoal', [
            'idGoal' => $idGoal,
            'name' => $name,
            'matchAttribute' => $matchAttribute,
            'pattern' => $pattern,
            'patternType' => $patternType,
            'caseSensitive' => $caseSensitive,
            'revenue' => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ], $optional);
    }

    /**
     * Delete a goal
     *
     * @param int $idGoal
     * @param array $optional
     * @return bool|object
     */
    public function deleteGoal($idGoal, $optional = [])
    {
        return $this->_request('Goals.deleteGoal', [
            'idGoal' => $idGoal,
        ], $optional);
    }

    /**
     * Get the SKU of the items
     *
     * @param string $abandonedCarts
     * @param array $optional
     * @return bool|object
     */
    public function getItemsSku($abandonedCarts, $optional = [])
    {
        return $this->_request('Goals.getItemsSku', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get the name of the items
     *
     * @param bool $abandonedCarts
     * @param array $optional
     * @return bool|object
     */
    public function getItemsName($abandonedCarts, $optional = [])
    {
        return $this->_request('Goals.getItemsName', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get the categories of the items
     *
     * @param bool $abandonedCarts
     * @param array $optional
     * @return bool|object
     */
    public function getItemsCategory($abandonedCarts, $optional = [])
    {
        return $this->_request('Goals.getItemsCategory', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get conversion rates from a goal
     *
     * @param string $segment
     * @param string $idGoal
     * @param array $columns
     * @param array $optional
     * @return bool|object
     */
    public function getGoal($segment = '', $idGoal = '', $columns = [], $optional = [])
    {
        return $this->_request('Goals.get', [
            'segment' => $segment,
            'idGoal' => $idGoal,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get information about a time period and it's conversion rates
     *
     * @param string $segment
     * @param string $idGoal
     * @param array $optional
     * @return bool|object
     */
    public function getDaysToConversion($segment = '', $idGoal = '', $optional = [])
    {
        return $this->_request('Goals.getDaysToConversion', [
            'segment' => $segment,
            'idGoal' => $idGoal,
        ], $optional);
    }

    /**
     * Get information about how many site visits create a conversion
     *
     * @param string $segment
     * @param string $idGoal
     * @param array $optional
     * @return bool|object
     */
    public function getVisitsUntilConversion($segment = '', $idGoal = '', $optional = [])
    {
        return $this->_request('Goals.getVisitsUntilConversion', [
            'segment' => $segment,
            'idGoal' => $idGoal,
        ], $optional);
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
     * @param string $graphType 'evolution', 'verticalBar', 'pie' or '3dPie'
     * @param string $outputType
     * @param string $columns
     * @param string $labels
     * @param string $showLegend
     * @param int|string $width
     * @param int|string $height
     * @param int|string $fontSize
     * @param string $legendFontSize
     * @param bool|string $aliasedGraph "by default, Graphs are "smooth" (anti-aliased). If you are
     *                              generating hundreds of graphs and are concerned with performance,
     *                              you can set aliasedGraph=0. This will disable anti aliasing and
     *                              graphs will be generated faster, but look less pretty."
     * @param string $idGoal
     * @param array $colors Use own colors instead of the default. The colors has to be in hexadecimal
     *                      value without '#'
     * @param array $optional
     * @return bool|object
     */
    public function getImageGraph(
        $apiModule,
        $apiAction,
        $graphType = '',
        $outputType = '0',
        $columns = '',
        $labels = '',
        $showLegend = '1',
        $width = '',
        $height = '',
        $fontSize = '9',
        $legendFontSize = '',
        $aliasedGraph = '1',
        $idGoal = '',
        $colors = [],
        $optional = []
    ) {
        return $this->_request('ImageGraph.get', [
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
        ], $optional);
    }

    /**
     * MODULE: LANGUAGES MANAGER
     * Get plugin insights
     */

    /**
     * Check if piwik can generate insights for current period
     *
     * @param array $optional
     * @return bool|object
     */
    public function canGenerateInsights($optional = [])
    {
        return $this->_request('Insights.canGenerateInsights', [], $optional);
    }

    /**
     * Get insights overview
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getInsightsOverview($segment, $optional = [])
    {
        return $this->_request('Insights.getInsightsOverview', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get movers and shakers overview
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getMoversAndShakersOverview($segment, $optional = [])
    {
        return $this->_request('Insights.getMoversAndShakersOverview', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get movers and shakers
     *
     * @param int $reportUniqueId
     * @param string $segment
     * @param int $comparedToXPeriods
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     * @param array $optional
     * @return bool|object
     */
    public function getMoversAndShakers(
        $reportUniqueId,
        $segment,
        $comparedToXPeriods = 1,
        $limitIncreaser = 4,
        $limitDecreaser = 4,
        $optional = []
    ) {
        return $this->_request('Insights.getMoversAndShakers', [
            'reportUniqueId' => $reportUniqueId,
            'segment' => $segment,
            'comparedToXPeriods' => $comparedToXPeriods,
            'limitIncreaser' => $limitIncreaser,
            'limitDecreaser' => $limitDecreaser,
        ], $optional);
    }

    /**
     * Get insights
     *
     * @param int $reportUniqueId
     * @param string $segment
     * @param int $limitIncreaser
     * @param int $limitDecreaser
     * @param string $filterBy
     * @param int $minImpactPercent (0-100)
     * @param int $minGrowthPercent (0-100)
     * @param int $comparedToXPeriods
     * @param string $orderBy
     * @param array $optional
     * @return bool|object
     */
    public function getInsights(
        $reportUniqueId,
        $segment,
        $limitIncreaser = 5,
        $limitDecreaser = 5,
        $filterBy = '',
        $minImpactPercent = 2,
        $minGrowthPercent = 20,
        $comparedToXPeriods = 1,
        $orderBy = 'absolute',
        $optional = []
    ) {
        return $this->_request('Insights.getInsights', [
            'reportUniqueId' => $reportUniqueId,
            'segment' => $segment,
            'limitIncreaser' => $limitIncreaser,
            'limitDecreaser' => $limitDecreaser,
            'filterBy' => $filterBy,
            'minImpactPercent' => $minImpactPercent,
            'minGrowthPercent' => $minGrowthPercent,
            'comparedToXPeriods' => $comparedToXPeriods,
            'orderBy' => $orderBy,
        ], $optional);
    }

    /**
     * MODULE: LANGUAGES MANAGER
     * Manage languages
     */

    /**
     * Proof if language is available
     *
     * @param string $languageCode
     * @param array $optional
     * @return bool|object
     */
    public function getLanguageAvailable($languageCode, $optional = [])
    {
        return $this->_request('LanguagesManager.isLanguageAvailable', [
            'languageCode' => $languageCode,
        ], $optional);
    }

    /**
     * Get all available languages
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAvailableLanguages($optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguages', [], $optional);
    }

    /**
     * Get all available languages with information
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAvailableLanguagesInfo($optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguagesInfo', [], $optional);
    }

    /**
     * Get all available languages with their names
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAvailableLanguageNames($optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguageNames', [], $optional);
    }

    /**
     * Get translations for a language
     *
     * @param string $languageCode
     * @param array $optional
     * @return bool|object
     */
    public function getTranslations($languageCode, $optional = [])
    {
        return $this->_request('LanguagesManager.getTranslationsForLanguage', [
            'languageCode' => $languageCode,
        ], $optional);
    }

    /**
     * Get the language for the user with the login $login
     *
     * @param string $login
     * @param array $optional
     * @return bool|object
     */
    public function getLanguageForUser($login, $optional = [])
    {
        return $this->_request('LanguagesManager.getLanguageForUser', [
            'login' => $login,
        ], $optional);
    }

    /**
     * Set the language for the user with the login $login
     *
     * @param string $login
     * @param string $languageCode
     * @param array $optional
     * @return bool|object
     */
    public function setLanguageForUser($login, $languageCode, $optional = [])
    {
        return $this->_request('LanguagesManager.setLanguageForUser', [
            'login' => $login,
            'languageCode' => $languageCode,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getCounters($lastMinutes = 60, $segment = '', $optional = [])
    {
        return $this->_request('Live.getCounters', [
            'lastMinutes' => $lastMinutes,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get information about the last visits
     *
     * @param string $segment
     * @param string $minTimestamp
     * @param string $doNotFetchActions
     * @param array $optional
     * @return bool|object
     * @internal param int $filterLimit
     * @internal param int $maxIdVisit
     */
    public function getLastVisitsDetails($segment = '', $minTimestamp = '', $doNotFetchActions = '', $optional = [])
    {
        return $this->_request('Live.getLastVisitsDetails', [
            'segment' => $segment,
            'minTimestamp' => $minTimestamp,
            'doNotFetchActions' => $doNotFetchActions,
        ], $optional);
    }

    /**
     * Get a profile for a visitor
     *
     * @param string $visitorId
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getVisitorProfile($visitorId = '', $segment = '', $optional = [])
    {
        return $this->_request('Live.getVisitorProfile', [
            'visitorId' => $visitorId,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the ID of the most recent visitor
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getMostRecentVisitorId($segment = '', $optional = [])
    {
        return $this->_request('Live.getMostRecentVisitorId', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: MOBILEMESSAGING
     * The MobileMessaging API lets you manage and access all the MobileMessaging plugin features
     * including : - manage SMS API credential - activate phone numbers - check remaining credits -
     * send SMS
     */

    /**
     * Checks if SMSAPI has been configured
     *
     * @param array $optional
     * @return bool|object
     */
    public function areSMSAPICredentialProvided($optional = [])
    {
        return $this->_request('MobileMessaging.areSMSAPICredentialProvided', [], $optional);
    }

    /**
     * Get list with sms provider
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSMSProvider($optional = [])
    {
        return $this->_request('MobileMessaging.getSMSProvider', [], $optional);
    }

    /**
     * Set SMSAPI credentials
     *
     * @param string $provider
     * @param string $apiKey
     * @param array $optional
     * @return bool|object
     */
    public function setSMSAPICredential($provider, $apiKey, $optional = [])
    {
        return $this->_request('MobileMessaging.setSMSAPICredential', [
            'provider' => $provider,
            'apiKey' => $apiKey,
        ], $optional);
    }

    /**
     * Add phone number
     *
     * @param string $phoneNumber
     * @param array $optional
     * @return bool|object
     */
    public function addPhoneNumber($phoneNumber, $optional = [])
    {
        return $this->_request('MobileMessaging.addPhoneNumber', [
            'phoneNumber' => $phoneNumber,
        ], $optional);
    }

    /**
     * Get credits left
     *
     * @param array $optional
     * @return mixed
     */
    public function getCreditLeft($optional = [])
    {
        return $this->_request('MobileMessaging.getCreditLeft', [], $optional);
    }

    /**
     * Remove phone number
     *
     * @param string $phoneNumber
     * @param array $optional
     * @return bool|object
     */
    public function removePhoneNumber($phoneNumber, $optional = [])
    {
        return $this->_request('MobileMessaging.removePhoneNumber', [
            'phoneNumber' => $phoneNumber,
        ], $optional);
    }

    /**
     * Validate phone number
     *
     * @param string $phoneNumber
     * @param string $verificationCode
     * @param array $optional
     * @return bool|object
     */
    public function validatePhoneNumber($phoneNumber, $verificationCode, $optional = [])
    {
        return $this->_request('MobileMessaging.validatePhoneNumber', [
            'phoneNumber' => $phoneNumber,
            'verificationCode' => $verificationCode,
        ], $optional);
    }

    /**
     * Delete SMSAPI credentials
     *
     * @param array $optional
     * @return bool|object
     */
    public function deleteSMSAPICredential($optional = [])
    {
        return $this->_request('MobileMessaging.deleteSMSAPICredential', [], $optional);
    }

    /**
     * Unknown
     *
     * @param $delegatedManagement
     * @param array $optional
     * @return bool|object
     */
    public function setDelegatedManagement($delegatedManagement, $optional = [])
    {
        return $this->_request('MobileMessaging.setDelegatedManagement', [
            'delegatedManagement' => $delegatedManagement,
        ], $optional);
    }

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDelegatedManagement($optional = [])
    {
        return $this->_request('MobileMessaging.getDelegatedManagement', [], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getMultiSites($segment = '', $enhanced = '', $optional = [])
    {
        return $this->_request('MultiSites.getAll', [
            'segment' => $segment,
            'enhanced' => $enhanced,
        ], $optional);
    }

    /**
     * Get key metrics about one of the sites the user manages
     *
     * @param string $segment
     * @param string $enhanced
     * @param array $optional
     * @return bool|object
     */
    public function getOne($segment = '', $enhanced = '', $optional = [])
    {
        return $this->_request('MultiSites.getOne', [
            'segment' => $segment,
            'enhanced' => $enhanced,
        ], $optional);
    }

    /**
     * MODULE: OVERLAY
     */

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     */
    public function getOverlayTranslations($optional = [])
    {
        return $this->_request('Overlay.getTranslations', [], $optional);
    }

    /**
     * Get overlay excluded query parameters
     *
     * @param array $optional
     * @return bool|object
     */
    public function getOverlayExcludedQueryParameters($optional = [])
    {
        return $this->_request('Overlay.getExcludedQueryParameters', [], $optional);
    }

    /**
     * Get overlay following pages
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getOverlayFollowingPages($segment = '', $optional = [])
    {
        return $this->_request('Overlay.getFollowingPages', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: PROVIDER
     * Get provider information
     */

    /**
     * Get information about visitors internet providers
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getProvider($segment = '', $optional = [])
    {
        return $this->_request('Provider.getProvider', [
            'segment' => $segment,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getReferrerType($segment = '', $typeReferrer = '', $optional = [])
    {
        return $this->_request('Referrers.getReferrerType', [
            'segment' => $segment,
            'typeReferrer' => $typeReferrer,
        ], $optional);
    }

    /**
     * Get all referrers
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAllReferrers($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getAll', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get referrer keywords
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getKeywords($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keywords for an url
     *
     * @param string $url
     * @param array $optional
     * @return bool|object
     */
    public function getKeywordsForPageUrl($url, $optional = [])
    {
        return $this->_request('Referrers.getKeywordsForPageUrl', [
            'url' => $url,
        ], $optional);
    }

    /**
     * Get keywords for an page title
     *
     * @param string $title
     * @param array $optional
     * @return bool|object
     */
    public function getKeywordsForPageTitle($title, $optional = [])
    {
        return $this->_request('Referrers.getKeywordsForPageTitle', [
            'title' => $title,
        ], $optional);
    }

    /**
     * Get search engines by keyword
     *
     * @param $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSearchEnginesFromKeywordId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Referrers.getSearchEnginesFromKeywordId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get search engines
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSearchEngines($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getSearchEngines', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get search engines by search engine ID
     *
     * @param string $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getKeywordsFromSearchEngineId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Referrers.getKeywordsFromSearchEngineId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get campaigns
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCampaigns($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getCampaigns', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keywords by campaign ID
     *
     * @param $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getKeywordsFromCampaignId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Referrers.getKeywordsFromCampaignId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get name
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingName($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getName', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keyword content from name id
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingKeywordContentFromNameId($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getKeywordContentFromNameId', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get keyword
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingKeyword($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getKeyword', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get source     *
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingSource($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getSource', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get medium
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingMedium($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getMedium', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get content
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingContent($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getContent', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get source and medium
     * from advanced campaign reporting
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingSourceMedium($segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getSourceMedium', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get name from source and medium by ID
     * from advanced campaign reporting
     *
     * @param int $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getAdvancedCampaignReportingNameFromSourceMediumId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('AdvancedCampaignReporting.getNameFromSourceMediumId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get website referrerals
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getWebsites($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getWebsites', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get urls by website ID
     *
     * @param string $idSubtable
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUrlsFromWebsiteId($idSubtable, $segment = '', $optional = [])
    {
        return $this->_request('Referrers.getUrlsFromWebsiteId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get social referrerals
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSocials($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getSocials', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get social referral urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUrlsForSocial($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getUrlsForSocial', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct search engines
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfSearchEngines($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getNumberOfDistinctSearchEngines', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct keywords
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfKeywords($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getNumberOfDistinctKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct campaigns
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfCampaigns($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getNumberOfDistinctCampaigns', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct websites
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfWebsites($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getNumberOfDistinctWebsites', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct websites urls
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfWebsitesUrls($segment = '', $optional = [])
    {
        return $this->_request('Referrers.getNumberOfDistinctWebsitesUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: SEO
     * Get SEO information
     */

    /**
     * Get the SEO rank of an url
     *
     * @param string $url
     * @param array $optional
     * @return bool|object
     */
    public function getSeoRank($url, $optional = [])
    {
        return $this->_request('SEO.getRank', [
            'url' => $url,
        ], $optional);
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
     * @param string $idSegment
     * @param array $optional
     * @return bool|object
     */
    public function addReport(
        $description,
        $period,
        $hour,
        $reportType,
        $reportFormat,
        $reports,
        $parameters,
        $idSegment = '',
        $optional = []
    ) {
        return $this->_request('ScheduledReports.addReport', [
            'description' => $description,
            'period' => $period,
            'hour' => $hour,
            'reportType' => $reportType,
            'reportFormat' => $reportFormat,
            'reports' => $reports,
            'parameters' => $parameters,
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Updated scheduled report
     *
     * @param int $idReport
     * @param string $description
     * @param string $period
     * @param string $hour
     * @param string $reportType
     * @param string $reportFormat
     * @param array $reports
     * @param string $parameters
     * @param string $idSegment
     * @param array $optional
     * @return bool|object
     */
    public function updateReport(
        $idReport,
        $description,
        $period,
        $hour,
        $reportType,
        $reportFormat,
        $reports,
        $parameters,
        $idSegment = '',
        $optional = []
    ) {
        return $this->_request('ScheduledReports.updateReport', [
            'idReport' => $idReport,
            'description' => $description,
            'period' => $period,
            'hour' => $hour,
            'reportType' => $reportType,
            'reportFormat' => $reportFormat,
            'reports' => $reports,
            'parameters' => $parameters,
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Delete scheduled report
     *
     * @param int $idReport
     * @param array $optional
     * @return bool|object
     */
    public function deleteReport($idReport, $optional = [])
    {
        return $this->_request('ScheduledReports.deleteReport', [
            'idReport' => $idReport,
        ], $optional);
    }

    /**
     * Get list of scheduled reports
     *
     * @param string $idReport
     * @param string $ifSuperUserReturnOnlySuperUserReports
     * @param string $idSegment
     * @param array $optional
     * @return bool|object
     */
    public function getReports(
        $idReport = '',
        $ifSuperUserReturnOnlySuperUserReports = '',
        $idSegment = '',
        $optional = []
    ) {
        return $this->_request('ScheduledReports.getReports', [
            'idReport' => $idReport,
            'ifSuperUserReturnOnlySuperUserReports' => $ifSuperUserReturnOnlySuperUserReports,
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Get list of scheduled reports
     *
     * @param int $idReport
     * @param string $language
     * @param string $outputType
     * @param string $reportFormat
     * @param string $parameters
     * @param array $optional
     * @return bool|object
     */
    public function generateReport(
        $idReport,
        $language = '',
        $outputType = '',
        $reportFormat = '',
        $parameters = '',
        $optional = []
    ) {
        return $this->_request('ScheduledReports.generateReport', [
            'idReport' => $idReport,
            'language' => $language,
            'outputType' => $outputType,
            'reportFormat' => $reportFormat,
            'parameters' => $parameters,
        ], $optional);
    }

    /**
     * Send scheduled reports
     *
     * @param int $idReport
     * @param string $force
     * @param array $optional
     * @return bool|object
     */
    public function sendReport($idReport, $force = '', $optional = [])
    {
        return $this->_request('ScheduledReports.sendReport', [
            'idReport' => $idReport,
            'force' => $force,
        ], $optional);
    }

    /**
     * MODULE: SEGMENT EDITOR
     */

    /**
     * Check if current user can add new segments
     *
     * @param array $optional
     * @return bool|object
     */
    public function isUserCanAddNewSegment($optional = [])
    {
        return $this->_request('SegmentEditor.isUserCanAddNewSegment', [], $optional);
    }

    /**
     * Delete a segment
     *
     * @param int $idSegment
     * @param array $optional
     * @return bool|object
     */
    public function deleteSegment($idSegment, $optional = [])
    {
        return $this->_request('SegmentEditor.delete', [
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Updates a segment
     *
     * @param int $idSegment
     * @param string $name
     * @param string $definition
     * @param string $autoArchive
     * @param string $enableAllUsers
     * @param array $optional
     * @return bool|object
     */
    public function updateSegment(
        $idSegment,
        $name,
        $definition,
        $autoArchive = '',
        $enableAllUsers = '',
        $optional = []
    ) {
        return $this->_request('SegmentEditor.update', [
            'idSegment' => $idSegment,
            'name' => $name,
            'definition' => $definition,
            'autoArchive' => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ], $optional);
    }

    /**
     * Updates a segment
     *
     * @param string $name
     * @param string $definition
     * @param string $autoArchive
     * @param string $enableAllUsers
     * @param array $optional
     * @return bool|object
     */
    public function addSegment($name, $definition, $autoArchive = '', $enableAllUsers = '', $optional = [])
    {
        return $this->_request('SegmentEditor.add', [
            'name' => $name,
            'definition' => $definition,
            'autoArchive' => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ], $optional);
    }

    /**
     * Get a segment
     *
     * @param int $idSegment
     * @param array $optional
     * @return bool|object
     */
    public function getSegment($idSegment, $optional = [])
    {
        return $this->_request('SegmentEditor.get', [
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Get all segments
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAllSegments($optional = [])
    {
        return $this->_request('SegmentEditor.getAll', [], $optional);
    }

    /**
     * MODULE: SITES MANAGER
     * Manage sites
     */

    /**
     * Get the JS tag of the current site
     *
     * @param string $piwikUrl
     * @param string $mergeSubdomains
     * @param string $groupPageTitlesByDomain
     * @param string $mergeAliasUrls
     * @param string $visitorCustomVariables
     * @param string $pageCustomVariables
     * @param string $customCampaignNameQueryParam
     * @param string $customCampaignKeywordParam
     * @param string $doNotTrack
     * @param string $disableCookies
     * @param array $optional
     * @return bool|object
     */
    public function getJavascriptTag(
        $piwikUrl,
        $mergeSubdomains = '',
        $groupPageTitlesByDomain = '',
        $mergeAliasUrls = '',
        $visitorCustomVariables = '',
        $pageCustomVariables = '',
        $customCampaignNameQueryParam = '',
        $customCampaignKeywordParam = '',
        $doNotTrack = '',
        $disableCookies = '',
        $optional = []
    ) {
        return $this->_request('SitesManager.getJavascriptTag', [
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
        ], $optional);
    }

    /**
     * Get image tracking code of the current site
     *
     * @param string $piwikUrl
     * @param string $actionName
     * @param string $idGoal
     * @param string $revenue
     * @param array $optional
     * @return bool|object
     */
    public function getImageTrackingCode(
        $piwikUrl,
        $actionName = '',
        $idGoal = '',
        $revenue = '',
        $optional = []
    ) {
        return $this->_request('SitesManager.getImageTrackingCode', [
            'piwikUrl' => $piwikUrl,
            'actionName' => $actionName,
            'idGoal' => $idGoal,
            'revenue' => $revenue,
        ], $optional);
    }

    /**
     * Get sites from a group
     *
     * @param string $group
     * @param array $optional
     * @return bool|object
     */
    public function getSitesFromGroup($group, $optional = [])
    {
        return $this->_request('SitesManager.getSitesFromGroup', [
            'group' => $group,
        ], $optional);
    }

    /**
     * Get all site groups
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesGroups($optional = [])
    {
        return $this->_request('SitesManager.getSitesGroups', [], $optional);
    }

    /**
     * Get information about the current site
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSiteInformation($optional = [])
    {
        return $this->_request('SitesManager.getSiteFromId', [], $optional);
    }

    /**
     * Get urls from current site
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSiteUrls($optional = [])
    {
        return $this->_request('SitesManager.getSiteUrlsFromId', [], $optional);
    }

    /**
     * Get all sites
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAllSites($optional = [])
    {
        return $this->_request('SitesManager.getAllSites', [], $optional);
    }

    /**
     * Get all sites with ID
     *
     * @param array $optional
     * @return bool|object
     */
    public function getAllSitesId($optional = [])
    {
        return $this->_request('SitesManager.getAllSitesId', [], $optional);
    }

    /**
     * Get all sites with the visit count since $timestamp
     *
     * @param string $timestamp
     * @param array $optional
     *
     * @deprecated 2.15.0 https://developer.piwik.org/changelog#piwik-2150
     * @return bool|object
     */
    public function getSitesIdWithVisits($timestamp, $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithVisits', [
            'timestamp' => $timestamp,
        ], $optional);
    }

    /**
     * Get all sites where the current user has admin access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesWithAdminAccess($optional = [])
    {
        return $this->_request('SitesManager.getSitesWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has view access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesWithViewAccess($optional = [])
    {
        return $this->_request('SitesManager.getSitesWithViewAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has a least view access
     *
     * @param string $limit
     * @param array $optional
     * @return bool|object
     */
    public function getSitesWithAtLeastViewAccess($limit = '', $optional = [])
    {
        return $this->_request('SitesManager.getSitesWithAtLeastViewAccess', [
            'limit' => $limit,
        ], $optional);
    }

    /**
     * Get all sites with ID where the current user has admin access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesIdWithAdminAccess($optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has view access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesIdWithViewAccess($optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithViewAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has at least view access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSitesIdWithAtLeastViewAccess($optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithAtLeastViewAccess', [], $optional);
    }

    /**
     * Get a site by it's URL
     *
     * @param string $url
     * @param array $optional
     * @return bool|object
     */
    public function getSitesIdFromSiteUrl($url, $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdFromSiteUrl', [
            'url' => $url,
        ], $optional);
    }

    /**
     * Add a site
     *
     * @param string $siteName
     * @param string $urls Comma separated list of urls
     * @param string $ecommerce
     * @param string $siteSearch
     * @param string $searchKeywordParameters
     * @param string $searchCategoryParameters
     * @param string $excludeIps
     * @param string $excludedQueryParameters
     * @param string $timezone
     * @param string $currency
     * @param string $group
     * @param string $startDate
     * @param string $excludedUserAgents
     * @param string $keepURLFragments
     * @param string $type
     * @param array $optional
     * @return bool|object
     */
    public function addSite(
        $siteName,
        $urls,
        $ecommerce = '',
        $siteSearch = '',
        $searchKeywordParameters = '',
        $searchCategoryParameters = '',
        $excludeIps = '',
        $excludedQueryParameters = '',
        $timezone = '',
        $currency = '',
        $group = '',
        $startDate = '',
        $excludedUserAgents = '',
        $keepURLFragments = '',
        $type = '',
        $optional = []
    ) {
        return $this->_request('SitesManager.addSite', [
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
        ], $optional);
    }

    /**
     * Delete current site
     *
     * @param array $optional
     * @return bool|object
     */
    public function deleteSite($optional = [])
    {
        return $this->_request('SitesManager.deleteSite', [], $optional);
    }

    /**
     * Add alias urls for the current site
     *
     * @param array $urls
     * @param array $optional
     * @return bool|object
     */
    public function addSiteAliasUrls($urls, $optional = [])
    {
        return $this->_request('SitesManager.addSiteAliasUrls', [
            'urls' => $urls,
        ], $optional);
    }

    /**
     * Set alias urls for the current site
     *
     * @param array $urls
     * @param array $optional
     * @return bool|object
     */
    public function setSiteAliasUrls($urls, $optional = [])
    {
        return $this->_request('SitesManager.setSiteAliasUrls', [
            'urls' => $urls,
        ], $optional);
    }

    /**
     * Get IP's for a specific range
     *
     * @param string $ipRange
     * @param array $optional
     * @return bool|object
     */
    public function getIpsForRange($ipRange, $optional = [])
    {
        return $this->_request('SitesManager.getIpsForRange', [
            'ipRange' => $ipRange,
        ], $optional);
    }

    /**
     * Set the global excluded IP's
     *
     * @param array $excludedIps
     * @param array $optional
     * @return bool|object
     */
    public function setExcludedIps($excludedIps, $optional = [])
    {
        return $this->_request('SitesManager.setGlobalExcludedIps', [
            'excludedIps' => $excludedIps,
        ], $optional);
    }

    /**
     * Set global search parameters
     *
     * @param $searchKeywordParameters
     * @param $searchCategoryParameters
     * @param array $optional
     * @return bool|object
     */
    public function setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters, $optional = [])
    {
        return $this->_request('SitesManager.setGlobalSearchParameters ', [
            'searchKeywordParameters' => $searchKeywordParameters,
            'searchCategoryParameters' => $searchCategoryParameters,
        ], $optional);
    }

    /**
     * Get search keywords
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSearchKeywordParametersGlobal($optional = [])
    {
        return $this->_request('SitesManager.getSearchKeywordParametersGlobal', [], $optional);
    }

    /**
     * Get search categories
     *
     * @param array $optional
     * @return bool|object
     */
    public function getSearchCategoryParametersGlobal($optional = [])
    {
        return $this->_request('SitesManager.getSearchCategoryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded query parameters
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExcludedParameters($optional = [])
    {
        return $this->_request('SitesManager.getExcludedQueryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded user agents
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExcludedUserAgentsGlobal($optional = [])
    {
        return $this->_request('SitesManager.getExcludedUserAgentsGlobal', [], $optional);
    }

    /**
     * Set the global excluded user agents
     *
     * @param array $excludedUserAgents
     * @param array $optional
     * @return bool|object
     */
    public function setGlobalExcludedUserAgents($excludedUserAgents, $optional = [])
    {
        return $this->_request('SitesManager.setGlobalExcludedUserAgents', [
            'excludedUserAgents' => $excludedUserAgents,
        ], $optional);
    }

    /**
     * Check if site specific user agent exclude is enabled
     *
     * @param array $optional
     * @return bool|object
     */
    public function isSiteSpecificUserAgentExcludeEnabled($optional = [])
    {
        return $this->_request('SitesManager.isSiteSpecificUserAgentExcludeEnabled', [], $optional);
    }

    /**
     * Set site specific user agent exclude
     *
     * @param int $enabled
     * @param array $optional
     * @return bool|object
     */
    public function setSiteSpecificUserAgentExcludeEnabled($enabled, $optional = [])
    {
        return $this->_request('SitesManager.setSiteSpecificUserAgentExcludeEnabled', [
            'enabled' => $enabled,
        ], $optional);
    }

    /**
     * Check if the url fragments should be global
     *
     * @param array $optional
     * @return bool|object
     */
    public function getKeepURLFragmentsGlobal($optional = [])
    {
        return $this->_request('SitesManager.getKeepURLFragmentsGlobal', [], $optional);
    }

    /**
     * Set the url fragments global
     *
     * @param int $enabled
     * @param array $optional
     * @return bool|object
     */
    public function setKeepURLFragmentsGlobal($enabled, $optional = [])
    {
        return $this->_request('SitesManager.setKeepURLFragmentsGlobal', [
            'enabled' => $enabled,
        ], $optional);
    }

    /**
     * Set the global excluded query parameters
     *
     * @param array $excludedQueryParameters
     * @param array $optional
     * @return bool|object
     */
    public function setExcludedParameters($excludedQueryParameters, $optional = [])
    {
        return $this->_request('SitesManager.setGlobalExcludedQueryParameters', [
            'excludedQueryParameters' => $excludedQueryParameters,
        ], $optional);
    }

    /**
     * Get the global excluded IP's
     *
     * @param array $optional
     * @return bool|object
     */
    public function getExcludedIps($optional = [])
    {
        return $this->_request('SitesManager.getExcludedIpsGlobal', [], $optional);
    }

    /**
     * Get the default currency
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultCurrency($optional = [])
    {
        return $this->_request('SitesManager.getDefaultCurrency', [], $optional);
    }

    /**
     * Set the default currency
     *
     * @param string $defaultCurrency
     * @param array $optional
     * @return bool|object
     */
    public function setDefaultCurrency($defaultCurrency, $optional = [])
    {
        return $this->_request('SitesManager.setDefaultCurrency', [
            'defaultCurrency' => $defaultCurrency,
        ], $optional);
    }

    /**
     * Get the default timezone
     *
     * @param array $optional
     * @return bool|object
     */
    public function getDefaultTimezone($optional = [])
    {
        return $this->_request('SitesManager.getDefaultTimezone', [], $optional);
    }

    /**
     * Set the default timezone
     *
     * @param string $defaultTimezone
     * @param array $optional
     * @return bool|object
     */
    public function setDefaultTimezone($defaultTimezone, $optional = [])
    {
        return $this->_request('SitesManager.setDefaultTimezone', [
            'defaultTimezone' => $defaultTimezone,
        ], $optional);
    }

    /**
     * Update current site
     *
     * @param string $siteName
     * @param array $urls
     * @param bool|string $ecommerce
     * @param bool|string $siteSearch
     * @param string $searchKeywordParameters
     * @param string $searchCategoryParameters
     * @param array|string $excludeIps
     * @param array|string $excludedQueryParameters
     * @param string $timezone
     * @param string $currency
     * @param string $group
     * @param string $startDate
     * @param string $excludedUserAgents
     * @param string $keepURLFragments
     * @param string $type
     * @param string $settings
     * @param array $optional
     * @return bool|object
     */
    public function updateSite(
        $siteName,
        $urls,
        $ecommerce = '',
        $siteSearch = '',
        $searchKeywordParameters = '',
        $searchCategoryParameters = '',
        $excludeIps = '',
        $excludedQueryParameters = '',
        $timezone = '',
        $currency = '',
        $group = '',
        $startDate = '',
        $excludedUserAgents = '',
        $keepURLFragments = '',
        $type = '',
        $settings = '',
        $optional = []
    ) {
        return $this->_request('SitesManager.updateSite', [
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
            'settings' => $settings,
        ], $optional);
    }

    /**
     * Get a list with all available currencies
     *
     * @param array $optional
     * @return bool|object
     */
    public function getCurrencyList($optional = [])
    {
        return $this->_request('SitesManager.getCurrencyList', [], $optional);
    }

    /**
     * Get a list with all currency symbols
     *
     * @param array $optional
     * @return bool|object
     */
    public function getCurrencySymbols($optional = [])
    {
        return $this->_request('SitesManager.getCurrencySymbols', [], $optional);
    }

    /**
     * Get a list with available timezones
     *
     * @param array $optional
     * @return bool|object
     */
    public function getTimezonesList($optional = [])
    {
        return $this->_request('SitesManager.getTimezonesList', [], $optional);
    }

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     */
    public function getUniqueSiteTimezones($optional = [])
    {
        return $this->_request('SitesManager.getUniqueSiteTimezones', [], $optional);
    }

    /**
     * Rename group
     *
     * @param string $oldGroupName
     * @param string $newGroupName
     * @param array $optional
     * @return bool|object
     */
    public function renameGroup($oldGroupName, $newGroupName, $optional = [])
    {
        return $this->_request('SitesManager.renameGroup', [
            'oldGroupName' => $oldGroupName,
            'newGroupName' => $newGroupName,
        ], $optional);
    }

    /**
     * Get all sites which matches the pattern
     *
     * @param string $pattern
     * @param array $optional
     * @return bool|object
     */
    public function getPatternMatchSites($pattern, $optional = [])
    {
        return $this->_request('SitesManager.getPatternMatchSites', [
            'pattern' => $pattern,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getTransitionsForPageTitle($pageTitle, $segment = '', $limitBeforeGrouping = '', $optional = [])
    {
        return $this->_request('Transitions.getTransitionsForPageTitle', [
            'pageTitle' => $pageTitle,
            'segment' => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ], $optional);
    }

    /**
     * Get transitions for a page URL
     *
     * @param $pageUrl
     * @param string $segment
     * @param string $limitBeforeGrouping
     * @param array $optional
     * @return bool|object
     */
    public function getTransitionsForPageUrl($pageUrl, $segment = '', $limitBeforeGrouping = '', $optional = [])
    {
        return $this->_request('Transitions.getTransitionsForPageTitle', [
            'pageUrl' => $pageUrl,
            'segment' => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ], $optional);
    }

    /**
     * Get transitions for a page URL
     *
     * @param $actionName
     * @param $actionType
     * @param string $segment
     * @param string $limitBeforeGrouping
     * @param string $parts
     * @param string $returnNormalizedUrls
     * @param array $optional
     * @return bool|object
     */
    public function getTransitionsForAction(
        $actionName,
        $actionType,
        $segment = '',
        $limitBeforeGrouping = '',
        $parts = 'all',
        $returnNormalizedUrls = '',
        $optional = []
    ) {
        return $this->_request('Transitions.getTransitionsForAction', [
            'actionName' => $actionName,
            'actionType' => $actionType,
            'segment' => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
            'parts' => $parts,
            'returnNormalizedUrls' => $returnNormalizedUrls,
        ], $optional);
    }

    /**
     * Get translations for the transitions
     *
     * @param array $optional
     * @return bool|object
     */
    public function getTransitionsTranslations($optional = [])
    {
        return $this->_request('Transitions.getTranslations', [], $optional);
    }

    /**
     * MODULE: USER COUNTRY
     * Get visitors country information
     */

    /**
     * Get countries of all visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCountry($segment = '', $optional = [])
    {
        return $this->_request('UserCountry.getCountry', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get continents of all visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getContinent($segment = '', $optional = [])
    {
        return $this->_request('UserCountry.getContinent', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get regions of all visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getRegion($segment = '', $optional = [])
    {
        return $this->_request('UserCountry.getRegion', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get cities of all visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCity($segment = '', $optional = [])
    {
        return $this->_request('UserCountry.getCity', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get location from ip
     *
     * @param string $ip
     * @param string $provider
     * @param array $optional
     * @return bool|object
     */
    public function getLocationFromIP($ip, $provider = '', $optional = [])
    {
        return $this->_request('UserCountry.getLocationFromIP', [
            'ip' => $ip,
            'provider' => $provider,
        ], $optional);
    }

    /**
     * Get the number of disting countries
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getCountryNumber($segment = '', $optional = [])
    {
        return $this->_request('UserCountry.getNumberOfDistinctCountries', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: USER Resultion
     * Get screen resolutions
     */

    /**
     * Get resolution
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getResolution($segment = '', $optional = [])
    {
        return $this->_request('Resolution.getResolution', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get configuration
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getConfiguration($segment = '', $optional = [])
    {
        return $this->_request('Resolution.getConfiguration', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: DEVICE PLUGINS
     * Get device plugins
     */

    /**
     * Get plugins
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUserPlugin($segment = '', $optional = [])
    {
        return $this->_request('DevicePlugins.getPlugin', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: USER LANGUAGE
     * Get the user language
     */

    /**
     * Get language
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUserLanguage($segment = '', $optional = [])
    {
        return $this->_request('UserLanguage.getLanguage', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get language code
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUserLanguageCode($segment = '', $optional = [])
    {
        return $this->_request('UserLanguage.getLanguageCode', [
            'segment' => $segment,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function setUserPreference($userLogin, $preferenceName, $preferenceValue, $optional = [])
    {
        return $this->_request('UsersManager.setUserPreference', [
            'userLogin' => $userLogin,
            'preferenceName' => $preferenceName,
            'preferenceValue' => $preferenceValue,
        ], $optional);
    }

    /**
     * Get user preference
     *
     * @param string $userLogin Username
     * @param string $preferenceName
     * @param array $optional
     * @return bool|object
     */
    public function getUserPreference($userLogin, $preferenceName, $optional = [])
    {
        return $this->_request('UsersManager.getUserPreference', [
            'userLogin' => $userLogin,
            'preferenceName' => $preferenceName,
        ], $optional);
    }

    /**
     * Get user by username
     *
     * @param string $userLogins Comma separated list with user names
     * @param array $optional
     * @return bool|object
     */
    public function getUsers($userLogins = '', $optional = [])
    {
        return $this->_request('UsersManager.getUsers', [
            'userLogins' => $userLogins,
        ], $optional);
    }

    /**
     * Get all user logins
     *
     * @param array $optional
     * @return bool|object
     */
    public function getUsersLogin($optional = [])
    {
        return $this->_request('UsersManager.getUsersLogin', [], $optional);
    }

    /**
     * Get sites by user access
     *
     * @param string $access
     * @param array $optional
     * @return bool|object
     */
    public function getUsersSitesFromAccess($access, $optional = [])
    {
        return $this->_request('UsersManager.getUsersSitesFromAccess', [
            'access' => $access,
        ], $optional);
    }

    /**
     * Get all users with access level from the current site
     *
     * @param array $optional
     * @return bool|object
     */
    public function getUsersAccess($optional = [])
    {
        return $this->_request('UsersManager.getUsersAccessFromSite', [], $optional);
    }

    /**
     * Get all users with access $access to the current site
     *
     * @param string $access
     * @param array $optional
     * @return bool|object
     */
    public function getUsersWithSiteAccess($access, $optional = [])
    {
        return $this->_request('UsersManager.getUsersWithSiteAccess', [
            'access' => $access,
        ], $optional);
    }

    /**
     * Get site access from the user $userLogin
     *
     * @param string $userLogin Username
     * @param array $optional
     * @return bool|object
     */
    public function getSitesAccessFromUser($userLogin, $optional = [])
    {
        return $this->_request('UsersManager.getSitesAccessFromUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Get user by login
     *
     * @param string $userLogin Username
     * @param array $optional
     * @return bool|object
     */
    public function getUser($userLogin, $optional = [])
    {
        return $this->_request('UsersManager.getUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Get user by email
     *
     * @param string $userEmail
     * @param array $optional
     * @return bool|object
     */
    public function getUserByEmail($userEmail, $optional = [])
    {
        return $this->_request('UsersManager.getUserByEmail', [
            'userEmail' => $userEmail,
        ], $optional);
    }

    /**
     * Add a user
     *
     * @param string $userLogin Username
     * @param string $password Password in clear text
     * @param string $email
     * @param string $alias
     * @param array $optional
     * @return bool|object
     */
    public function addUser($userLogin, $password, $email, $alias = '', $optional = [])
    {
        return $this->_request('UsersManager.addUser', [
            'userLogin' => $userLogin,
            'password' => $password,
            'email' => $email,
            'alias' => $alias,
        ], $optional);
    }

    /**
     * Set super user access
     *
     * @param string $userLogin Username
     * @param int $hasSuperUserAccess
     * @param array $optional
     * @return bool|object
     */
    public function setSuperUserAccess($userLogin, $hasSuperUserAccess, $optional = [])
    {
        return $this->_request('UsersManager.setSuperUserAccess', [
            'userLogin' => $userLogin,
            'hasSuperUserAccess' => $hasSuperUserAccess,
        ], $optional);
    }

    /**
     * Check if user has super user access
     *
     * @param array $optional
     * @return bool|object
     */
    public function hasSuperUserAccess($optional = [])
    {
        return $this->_request('UsersManager.hasSuperUserAccess', [], $optional);
    }

    /**
     * Get a list of users with super user access
     *
     * @param array $optional
     * @return bool|object
     */
    public function getUsersHavingSuperUserAccess($optional = [])
    {
        return $this->_request('UsersManager.getUsersHavingSuperUserAccess', [], $optional);
    }

    /**
     * Update a user
     *
     * @param string $userLogin Username
     * @param string $password Password in clear text
     * @param string $email
     * @param string $alias
     * @param array $optional
     * @return bool|object
     */
    public function updateUser($userLogin, $password = '', $email = '', $alias = '', $optional = [])
    {
        return $this->_request('UsersManager.updateUser', [
            'userLogin' => $userLogin,
            'password' => $password,
            'email' => $email,
            'alias' => $alias,
        ], $optional);
    }

    /**
     * Delete a user
     *
     * @param string $userLogin Username
     * @param array $optional
     * @return bool|object
     */
    public function deleteUser($userLogin, $optional = [])
    {
        return $this->_request('UsersManager.deleteUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Checks if a user exist
     *
     * @param string $userLogin
     * @param array $optional
     * @return bool|object
     */
    public function userExists($userLogin, $optional = [])
    {
        return $this->_request('UsersManager.userExists', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Checks if a user exist by email
     *
     * @param string $userEmail
     * @param array $optional
     * @return bool|object
     */
    public function userEmailExists($userEmail, $optional = [])
    {
        return $this->_request('UsersManager.userEmailExists', [
            'userEmail' => $userEmail,
        ], $optional);
    }

    /**
     * Grant access to multiple sites
     *
     * @param string $userLogin Username
     * @param string $access
     * @param array $idSites
     * @param array $optional
     * @return bool|object
     */
    public function setUserAccess($userLogin, $access, $idSites, $optional = [])
    {
        return $this->_request('UsersManager.setUserAccess', [
            'userLogin' => $userLogin,
            'access' => $access,
            'idSites' => $idSites,
        ], $optional);
    }

    /**
     * Get the token for a user
     *
     * @param string $userLogin Username
     * @param string $md5Password Password in clear text
     * @param array $optional
     * @return bool|object
     */
    public function getTokenAuth($userLogin, $md5Password, $optional = [])
    {
        return $this->_request('UsersManager.getTokenAuth', [
            'userLogin' => $userLogin,
            'md5Password' => md5($md5Password),
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getVisitFrequency($segment = '', $columns = '', $optional = [])
    {
        return $this->_request('VisitFrequency.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * MODULE: VISIT TIME
     * Get visit time
     */

    /**
     * Get the visit by local time
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getVisitLocalTime($segment = '', $optional = [])
    {
        return $this->_request('VisitTime.getVisitInformationPerLocalTime', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the visit by server time
     *
     * @param string $segment
     * @param string $hideFutureHoursWhenToday Hide the future hours when the report is created for today
     * @param array $optional
     * @return bool|object
     */
    public function getVisitServerTime($segment = '', $hideFutureHoursWhenToday = '', $optional = [])
    {
        return $this->_request('VisitTime.getVisitInformationPerServerTime', [
            'segment' => $segment,
            'hideFutureHoursWhenToday' => $hideFutureHoursWhenToday,
        ], $optional);
    }

    /**
     * Get the visit by server time
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getByDayOfWeek($segment = '', $optional = [])
    {
        return $this->_request('VisitTime.getByDayOfWeek', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: VISITOR INTEREST
     * Get the interests of the visitor
     */

    /**
     * Get the number of visits per visit duration
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfVisitsPerDuration($segment = '', $optional = [])
    {
        return $this->_request('VisitorInterest.getNumberOfVisitsPerVisitDuration', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of visits per visited page
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfVisitsPerPage($segment = '', $optional = [])
    {
        return $this->_request('VisitorInterest.getNumberOfVisitsPerPage', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of days elapsed since the last visit
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfVisitsByDaySinceLast($segment = '', $optional = [])
    {
        return $this->_request('VisitorInterest.getNumberOfVisitsByDaysSinceLast', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of visits by visit count
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getNumberOfVisitsByCount($segment = '', $optional = [])
    {
        return $this->_request('VisitorInterest.getNumberOfVisitsByVisitCount', [
            'segment' => $segment,
        ], $optional);
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
     * @param array $optional
     * @return bool|object
     */
    public function getVisitsSummary($segment = '', $columns = '', $optional = [])
    {
        return $this->_request('VisitsSummary.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get visits
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getVisits($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getVisits', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get unique visits
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUniqueVisitors($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getUniqueVisitors', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get user visit summary
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getUserVisitors($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getUsers', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get actions
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getActions($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getActions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get max actions
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getMaxActions($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getMaxActions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get bounce count
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getBounceCount($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getBounceCount', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get converted visits
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getVisitsConverted($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getVisitsConverted', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the sum of all visit lengths
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSumVisitsLength($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getSumVisitsLength', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the sum of all visit lengths formated in the current language
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     */
    public function getSumVisitsLengthPretty($segment = '', $optional = [])
    {
        return $this->_request('VisitsSummary.getSumVisitsLengthPretty', [
            'segment' => $segment,
        ], $optional);
    }
}
