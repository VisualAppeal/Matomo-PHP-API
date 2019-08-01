<?php namespace VisualAppeal;

use InvalidArgumentException;

use Httpful\Exception\NetworkErrorException;
use Httpful\Request;
use Httpful\Response;

/**
 * Repository: https://github.com/VisualAppeal/Matomo-PHP-API
 * Official api reference: https://developer.matomo.org/api-reference/reporting-api
 */
class Matomo
{
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
    const FORMAT_ORIGINAL = 'original';

    /**
     * @var string URL of the matomo installation
     */
    private $_site = '';

    /**
     * @var string API Access token
     */
    private $_token = '';

    /**
     * @var mixed The integer id of your website.
     */
    private $_siteId = null;

    /**
     * @var string The period you request the statistics for.
     */
    private $_period = self::PERIOD_DAY;

    /**
     * @var string
     */
    private $_date = '';

    /**
     * @var string Defines the format of the output.
     */
    private $_format = self::FORMAT_PHP;

    /**
     * @var int Defines the number of rows to be returned (-1: All rows).
     */
    private $_filter_limit = 100;

    /**
     * @var string Returns data strings that can be internationalized and will be translated.
     */
    private $_language = 'en';

    /**
     * @var string
     */
    private $_rangeStart = 'yesterday';

    /**
     * @var string|null
     */
    private $_rangeEnd = null;

    /**
     * @var bool
     */
    private $_isJsonDecodeAssoc = false;

    /**
     * @var bool If the certificate of the matomo installation should be verified.
     */
    private $_verifySsl = false;

    /**
     * @var int How many redirects curl should execute until aborting.
     */
    private $_maxRedirects = 5;

    /**
     * Create a new instance.
     *
     * @param string $site URL of the matomo installation
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
        $siteId = null,
        $format = self::FORMAT_JSON,
        $period = self::PERIOD_DAY,
        $date = self::DATE_YESTERDAY,
        $rangeStart = '',
        $rangeEnd = null
    )
    {
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
     * Get the url of the matomo installation
     *
     * @return string
     */
    public function getSite(): string
    {
        return $this->_site;
    }

    /**
     * Set the URL of the matomo installation
     *
     * @param string $url
     * @return $this
     */
    public function setSite(string $url): Matomo
    {
        $this->_site = $url;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->_token;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): Matomo
    {
        $this->_token = $token;

        return $this;
    }

    /**
     * Get current site ID
     *
     * @return mixed
     */
    public function getSiteId()
    {
        return $this->_siteId;
    }

    /**
     * Set current site ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setSiteId($id = null): Matomo
    {
        $this->_siteId = $id;

        return $this;
    }

    /**
     * Get response format
     *
     * @return string
     */
    public function getFormat(): string
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
    public function setFormat(string $format): Matomo
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->_language;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return $this
     */
    public function setLanguage(string $language): Matomo
    {
        $this->_language = $language;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate(): string
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
    public function setDate(string $date = null): Matomo
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
    public function getPeriod(): string
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
    public function setPeriod(string $period)
    {
        $this->_period = $period;

        return $this;
    }

    /**
     * Get the date range comma separated
     *
     * @return string
     */
    public function getRange(): string
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
    public function setRange(string $rangeStart = null, string $rangeEnd = null): Matomo
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
     * Get the number rows which should be returned
     *
     * @return int
     */
    public function getFilterLimit(): int
    {
        return $this->_filter_limit;
    }

    /**
     * Set the number of rows which should be returned
     *
     * @param int $filterLimit
     * @return $this
     */
    public function setFilterLimit(int $filterLimit): Matomo
    {
        $this->_filter_limit = $filterLimit;

        return $this;
    }

    /**
     * Return if JSON decode an associate array
     *
     * @return bool
     */
    public function isJsonDecodeAssoc(): bool
    {
        return $this->_isJsonDecodeAssoc;
    }

    /**
     * Sets the json_decode format
     *
     * @param bool $isJsonDecodeAssoc false decode as Object, true for decode as Associate array
     * @return $this
     */
    public function setIsJsonDecodeAssoc(bool $isJsonDecodeAssoc): Matomo
    {
        $this->_isJsonDecodeAssoc = $isJsonDecodeAssoc;

        return $this;
    }

    /**
     * If the certificate of the matomo installation should be verified.
     *
     * @return bool
     */
    public function getVerifySsl(): bool
    {
        return $this->_verifySsl;
    }

    /**
     * Set if the certificate of the matomo installation should be verified.
     *
     * @param bool $verifySsl
     * @return Matomo
     */
    public function setVerifySsl(bool $verifySsl): Matomo
    {
        $this->_verifySsl = $verifySsl;

        return $this;
    }

    /**
     * How many redircts curl should execute unitil aborting.
     *
     * @return int
     */
    public function getMaxRedirects(): int
    {
        return $this->_maxRedirects;
    }

    /**
     * Set how many redircts curl should execute unitil aborting.
     *
     * @param int $maxRedirects
     * @return Matomo
     */
    public function setMaxRedirects(int $maxRedirects): Matomo
    {
        $this->_maxRedirects = $maxRedirects;

        return $this;
    }

    /**
     * Reset all default variables.
     */
    public function reset(): Matomo
    {
        $this->_period = self::PERIOD_DAY;
        $this->_date = '';
        $this->_rangeStart = 'yesterday';
        $this->_rangeEnd = null;

        return $this;
    }

    /**
     * Requests to matomo api
     */

    /**
     * Make API request
     *
     * @param string $method
     * @param array $params
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    private function _request(string $method, array $params = [], array $optional = [])
    {
        $url = $this->_parseUrl($method, $params + $optional);
        if ($url === false) {
            throw new InvalidRequestException('Could not parse URL!');
        }

        $req = Request::get($url);
        if ($this->_verifySsl) {
			$req->enableStrictSSL();
		} else {
        	$req->disableStrictSSL();
		}
        $req->followRedirects($this->_maxRedirects);
        $req->withTimeout(5);

        try {
            $buffer = $req->send();
        } catch (NetworkErrorException $e) {
            throw new InvalidRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!empty($buffer)) {
            try {
                return $this->_finishResponse($this->_parseResponse($buffer), $method, $params + $optional);
            } catch (InvalidResponseException $e) {
                throw new InvalidRequestException($e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new InvalidRequestException('Empty response!');
    }

    /**
     * Validate request and return the values.
     *
     * @param mixed $response
     * @param string $method
     * @param array $params
     * @return bool|object
     * @throws InvalidResponseException
     */
    private function _finishResponse($response, string $method, array $params)
    {
        $valid = $this->_isValidResponse($response);

        if ($valid === true) {
            if (isset($response->value)) {
                return $response->value;
            } else {
                return $response;
            }
        } else {
            throw new InvalidResponseException($valid . ' (' . $this->_parseUrl($method, $params) . ')');
        }
    }

    /**
     * Create request url with parameters
     *
     * @param string $method The request method
     * @param array $params Request params
     * @return string|false
     * @throws InvalidArgumentException
     */
    private function _parseUrl(string $method, array $params = [])
    {
        $params = [
                'module' => 'API',
                'method' => $method,
                'token_auth' => $this->_token,
                'idSite' => $this->_siteId,
                'period' => $this->_period,
                'format' => $this->_format,
                'language' => $this->_language,
                'filter_limit' => $this->_filter_limit
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
            throw new InvalidArgumentException('Specify a date or a date range!');
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
     * Check if the request was successfull.
     *
     * @param mixed $response
     * @return bool|int
     */
    private function _isValidResponse($response)
    {
        if (is_null($response)) {
            return self::ERROR_EMPTY;
        }

        if (!isset($response->result) or ($response->result != 'error')) {
            return true;
        }

        return $response->message;
    }

    /**
     * Parse request result
     *
     * @param Response $response
     * @return mixed
     */
    private function _parseResponse(Response $response)
    {
        switch ($this->_format) {
            case self::FORMAT_JSON:
                return json_decode($response, $this->_isJsonDecodeAssoc);
                break;
            default:
                return $response;
        }
    }

    /**
     * MODULE: API
     * API metadata
     */

    /**
     * Get current matomo version
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getMatomoVersion(array $optional = [])
    {
        return $this->_request('API.getMatomoVersion', [], $optional);
    }

    /**
     * Get current ip address (from the server executing this script)
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getIpFromHeader(array $optional = [])
    {
        return $this->_request('API.getIpFromHeader', [], $optional);
    }

    /**
     * Get current settings
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSettings(array $optional = [])
    {
        return $this->_request('API.getSettings', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getDefaultMetricTranslations(array $optional = [])
    {
        return $this->_request('API.getDefaultMetricTranslations', [], $optional);
    }

    /**
     * Get default metrics
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getDefaultMetrics(array $optional = [])
    {
        return $this->_request('API.getDefaultMetrics', [], $optional);
    }

    /**
     * Get default processed metrics
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getDefaultProcessedMetrics(array $optional = [])
    {
        return $this->_request('API.getDefaultProcessedMetrics', [], $optional);
    }

    /**
     * Get default metrics documentation
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getDefaultMetricsDocumentation(array $optional = [])
    {
        return $this->_request('API.getDefaultMetricsDocumentation', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param array $sites Array with the ID's of the sites
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSegmentsMetadata($sites = [], array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getLogoUrl($pathOnly = false, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getHeaderLogoUrl($pathOnly = false, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMetadata($apiModule, $apiAction, $apiParameters = [], array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getReportMetadata(
        array $idSites,
        $hideMetricsDoc = '',
        $showSubtableReports = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function getProcessedReport(
        $apiModule,
        $apiAction,
        $segment = '',
        $apiParameters = '',
        $idGoal = '',
        $showTimer = '1',
        $hideMetricsDoc = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function getApi($segment = '', $columns = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getRowEvolution(
        $apiModule,
        $apiAction,
        $segment = '',
        $column = '',
        $idGoal = '',
        $legendAppendMetric = '1',
        $labelUseAbsoluteUrl = '1',
        array $optional = []
    )
    {
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
     * Get the result of multiple requests bundled together
     * Take as an argument an array of the API methods to send together
     * For example, ['API.get', 'Action.get', 'DeviceDetection.getType']
     *
     * @param array $methods
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getBulkRequest($methods = [], array $optional = [])
    {
        $urls = [];

        foreach ($methods as $key => $method) {
            $urls['urls[' . $key . ']'] = urlencode('method=' . $method);
        }

        return $this->_request('API.getBulkRequest', $urls, $optional);
    }

    /**
     * Get a list of available widgets.
     *
     * @return object
     * @throws InvalidRequestException
     */
    public function getWidgetMetadata()
    {
        return $this->_request('API.getWidgetMetadata');
    }

    /**
     * Get a list of all available pages that exist including the widgets they include.
     *
     * @return object
     * @throws InvalidRequestException
     */
    public function getReportPagesMetadata()
    {
        return $this->_request('API.getReportPagesMetadata');
    }

    /**
     * Get suggested values for segments
     *
     * @param string $segmentName
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSuggestedValuesForSegment($segmentName, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAction($segment = '', $columns = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageUrls($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageUrlsFollowingSiteSearch($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageTitlesFollowingSiteSearch($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getEntryPageUrls($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExitPageUrls($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageUrl($pageUrl, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageTitles($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getEntryPageTitles($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExitPageTitles($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPageTitle($pageName, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDownloads($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDownload($downloadUrl, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOutlinks($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOutlink($outlinkUrl, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSiteSearchKeywords($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSiteSearchNoResultKeywords($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSiteSearchCategories($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function addAnnotation($note, $starred = 0, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function saveAnnotation($idNote, $note = '', $starred = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function deleteAnnotation($idNote, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function deleteAllAnnotations(array $optional = [])
    {
        return $this->_request('Annotations.deleteAll', [], $optional);
    }

    /**
     * Get annotation
     *
     * @param int $idNote
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getAnnotation($idNote, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAllAnnotation($lastN = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAnnotationCountForDates($lastN, $getAnnotationText, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getContentNames($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getContentPieces($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAlert($idAlert, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getValuesForAlertInPast($idAlert, $subPeriodN, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAlerts($idSites, $ifSuperUserReturnAllAlerts = '', array $optional = [])
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function deleteAlert($idAlert, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getTriggeredAlerts($idSites, array $optional = [])
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
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getCustomDimension($idDimension, array $optional = [])
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
     * @param int $active '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $optional
     *
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function configureNewCustomDimension($name, $scope, $active, array $optional = [])
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
     * @param int $idDimension The id of a Custom Dimension.
     * @param string $name The name of the dimension
     * @param int $active '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $optional
     *
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function configureExistingCustomDimension($idDimension, $name, $active, array $optional = [])
    {
        return $this->_request('CustomDimensions.configureExistingCustomDimension', [
            'idDimension' => $idDimension,
            'name' => $name,
            'active' => $active,
        ], $optional);
    }

    /**
     * @return bool|object
     * @throws InvalidRequestException
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
     * @throws InvalidRequestException
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
     * @throws InvalidRequestException
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
     * @return bool|array
     * @throws InvalidRequestException
     */
    public function getCustomVariables($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCustomVariable($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDashboards(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDeviceType($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDeviceBrand($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDeviceModel($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOSFamilies($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOsVersions($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getBrowsers($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getBrowserVersions($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getBrowserEngines($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getEventCategory($segment = '', $secondaryDimension = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getEventAction($segment = '', $secondaryDimension = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getEventName($segment = '', $secondaryDimension = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getActionFromCategoryId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNameFromCategoryId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCategoryFromActionId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNameFromActionId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getActionFromNameId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCategoryFromNameId($idSubtable, $segment = '', array $optional = [])
    {
        return $this->_request('Events.getCategoryFromNameId', [
            'idSubtable' => $idSubtable,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: EXAMPLE API
     * Get api and matomo information
     */

    /**
     * Get the matomo version
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleMatomoVersion(array $optional = [])
    {
        return $this->_request('ExampleAPI.getMatomoVersion', [], $optional);
    }

    /**
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleAnswerToLife(array $optional = [])
    {
        return $this->_request('ExampleAPI.getAnswerToLife', [], $optional);
    }

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleObject(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExampleSum($a = 0, $b = 0, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExampleNull(array $optional = [])
    {
        return $this->_request('ExampleAPI.getNull', [], $optional);
    }

    /**
     * Get a short matomo description
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleDescriptionArray(array $optional = [])
    {
        return $this->_request('ExampleAPI.getDescriptionArray', [], $optional);
    }

    /**
     * Get a short comparison with other analytic software
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleCompetitionDatatable(array $optional = [])
    {
        return $this->_request('ExampleAPI.getCompetitionDatatable', [], $optional);
    }

    /**
     * Get information about 42
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleMoreInformationAnswerToLife(array $optional = [])
    {
        return $this->_request('ExampleAPI.getMoreInformationAnswerToLife', [], $optional);
    }

    /**
     * Get a multidimensional array
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExampleMultiArray(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExamplePluginAnswerToLife($truth = 1, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExamplePluginReport($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function sendFeedbackForFeature($featureName, $like, $message = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getGoals(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function addGoal(
        $name,
        $matchAttribute,
        $pattern,
        $patternType,
        $caseSensitive = '',
        $revenue = '',
        $allowMultipleConversionsPerVisit = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function deleteGoal($idGoal, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getItemsSku($abandonedCarts, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getItemsName($abandonedCarts, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getItemsCategory($abandonedCarts, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getGoal($segment = '', $idGoal = '', $columns = [], array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDaysToConversion($segment = '', $idGoal = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitsUntilConversion($segment = '', $idGoal = '', array $optional = [])
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * Check if matomo can generate insights for current period
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function canGenerateInsights(array $optional = [])
    {
        return $this->_request('Insights.canGenerateInsights', [], $optional);
    }

    /**
     * Get insights overview
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getInsightsOverview($segment, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMoversAndShakersOverview($segment, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMoversAndShakers(
        $reportUniqueId,
        $segment,
        $comparedToXPeriods = 1,
        $limitIncreaser = 4,
        $limitDecreaser = 4,
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function getLanguageAvailable($languageCode, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAvailableLanguages(array $optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguages', [], $optional);
    }

    /**
     * Get all available languages with information
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getAvailableLanguagesInfo(array $optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguagesInfo', [], $optional);
    }

    /**
     * Get all available languages with their names
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getAvailableLanguageNames(array $optional = [])
    {
        return $this->_request('LanguagesManager.getAvailableLanguageNames', [], $optional);
    }

    /**
     * Get translations for a language
     *
     * @param string $languageCode
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getTranslations($languageCode, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getLanguageForUser($login, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setLanguageForUser($login, $languageCode, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCounters($lastMinutes = 60, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     * @internal param int $maxIdVisit
     * @internal param int $filterLimit
     */
    public function getLastVisitsDetails($segment = '', $minTimestamp = '', $doNotFetchActions = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitorProfile($visitorId = '', $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMostRecentVisitorId($segment = '', array $optional = [])
    {
        return $this->_request('Live.getMostRecentVisitorId', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get userId for visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getUsersById($segment = '', array $optional = [])
    {
        return $this->_request('UserId.getUsers', [
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
     * @throws InvalidRequestException
     */
    public function areSMSAPICredentialProvided(array $optional = [])
    {
        return $this->_request('MobileMessaging.areSMSAPICredentialProvided', [], $optional);
    }

    /**
     * Get list with sms provider
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSMSProvider(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setSMSAPICredential($provider, $apiKey, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function addPhoneNumber($phoneNumber, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCreditLeft(array $optional = [])
    {
        return $this->_request('MobileMessaging.getCreditLeft', [], $optional);
    }

    /**
     * Remove phone number
     *
     * @param string $phoneNumber
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function removePhoneNumber($phoneNumber, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function validatePhoneNumber($phoneNumber, $verificationCode, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function deleteSMSAPICredential(array $optional = [])
    {
        return $this->_request('MobileMessaging.deleteSMSAPICredential', [], $optional);
    }

    /**
     * Unknown
     *
     * @param $delegatedManagement
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setDelegatedManagement($delegatedManagement, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDelegatedManagement(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMultiSites($segment = '', $enhanced = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOne($segment = '', $enhanced = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getOverlayTranslations(array $optional = [])
    {
        return $this->_request('Overlay.getTranslations', [], $optional);
    }

    /**
     * Get overlay excluded query parameters
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getOverlayExcludedQueryParameters(array $optional = [])
    {
        return $this->_request('Overlay.getExcludedQueryParameters', [], $optional);
    }

    /**
     * Get overlay following pages
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getOverlayFollowingPages($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getProvider($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getReferrerType($segment = '', $typeReferrer = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAllReferrers($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeywords($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeywordsForPageUrl($url, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeywordsForPageTitle($title, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSearchEnginesFromKeywordId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSearchEngines($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeywordsFromSearchEngineId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCampaigns($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeywordsFromCampaignId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingName($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingKeywordContentFromNameId($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingKeyword($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingSource($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingMedium($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingContent($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingSourceMedium($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAdvancedCampaignReportingNameFromSourceMediumId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getWebsites($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUrlsFromWebsiteId($idSubtable, $segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSocials($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUrlsForSocial($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfSearchEngines($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfKeywords($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfCampaigns($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfWebsites($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfWebsitesUrls($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSeoRank($url, array $optional = [])
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function deleteReport($idReport, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getReports(
        $idReport = '',
        $ifSuperUserReturnOnlySuperUserReports = '',
        $idSegment = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function generateReport(
        $idReport,
        $language = '',
        $outputType = '',
        $reportFormat = '',
        $parameters = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function sendReport($idReport, $force = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function isUserCanAddNewSegment(array $optional = [])
    {
        return $this->_request('SegmentEditor.isUserCanAddNewSegment', [], $optional);
    }

    /**
     * Delete a segment
     *
     * @param int $idSegment
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function deleteSegment($idSegment, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function updateSegment(
        $idSegment,
        $name,
        $definition,
        $autoArchive = '',
        $enableAllUsers = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function addSegment($name, $definition, $autoArchive = '', $enableAllUsers = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSegment($idSegment, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getAllSegments(array $optional = [])
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
     * @param string $matomoUrl
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
     * @throws InvalidRequestException
     */
    public function getJavascriptTag(
        $matomoUrl,
        $mergeSubdomains = '',
        $groupPageTitlesByDomain = '',
        $mergeAliasUrls = '',
        $visitorCustomVariables = '',
        $pageCustomVariables = '',
        $customCampaignNameQueryParam = '',
        $customCampaignKeywordParam = '',
        $doNotTrack = '',
        $disableCookies = '',
        array $optional = []
    )
    {
        return $this->_request('SitesManager.getJavascriptTag', [
            'piwikUrl' => $matomoUrl,
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
     * @param string $matomoUrl
     * @param string $actionName
     * @param string $idGoal
     * @param string $revenue
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getImageTrackingCode(
        $matomoUrl,
        $actionName = '',
        $idGoal = '',
        $revenue = '',
        array $optional = []
    )
    {
        return $this->_request('SitesManager.getImageTrackingCode', [
            'piwikUrl' => $matomoUrl,
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
     * @throws InvalidRequestException
     */
    public function getSitesFromGroup($group, array $optional = [])
    {
        return $this->_request('SitesManager.getSitesFromGroup', [
            'group' => $group,
        ], $optional);
    }

    /**
     * Get all site groups.
	 * Requires superuser access.
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesGroups(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesGroups', [], $optional);
    }

    /**
     * Get information about the current site
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSiteInformation(array $optional = [])
    {
        return $this->_request('SitesManager.getSiteFromId', [], $optional);
    }

    /**
     * Get urls from current site
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSiteUrls(array $optional = [])
    {
        return $this->_request('SitesManager.getSiteUrlsFromId', [], $optional);
    }

    /**
     * Get all sites
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getAllSites(array $optional = [])
    {
        return $this->_request('SitesManager.getAllSites', [], $optional);
    }

    /**
     * Get all sites with ID
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getAllSitesId(array $optional = [])
    {
        return $this->_request('SitesManager.getAllSitesId', [], $optional);
    }

    /**
     * Get all sites where the current user has admin access
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesWithAdminAccess(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has view access
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesWithViewAccess(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesWithViewAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has a least view access
     *
     * @param string $limit
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesWithAtLeastViewAccess($limit = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSitesIdWithAdminAccess(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has view access
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesIdWithViewAccess(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithViewAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has at least view access
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesIdWithAtLeastViewAccess(array $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdWithAtLeastViewAccess', [], $optional);
    }

    /**
     * Get a site by it's URL
     *
     * @param string $url
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSitesIdFromSiteUrl($url, array $optional = [])
    {
        return $this->_request('SitesManager.getSitesIdFromSiteUrl', [
            'url' => $url,
        ], $optional);
    }

    /**
     * Get a list of all available settings for a specific site.
     *
     * @return object
     * @throws InvalidRequestException
     */
    public function getSiteSettings()
    {
        return $this->_request('SitesManager.getSiteSettings');
    }

    /**
     * Add a website.
     * Requires Super User access.
     *
     * The website is defined by a name and an array of URLs.
     * @param string $siteName Site name
     * @param string $urls Comma separated list of urls
     * @param string $ecommerce Is Ecommerce Reporting enabled for this website?
     * @param string $siteSearch
     * @param string $searchKeywordParameters Comma separated list of search keyword parameter names
     * @param string $searchCategoryParameters Comma separated list of search category parameter names
     * @param string $excludeIps Comma separated list of IPs to exclude from the reports (allows wildcards)
     * @param string $excludedQueryParameters
     * @param string $timezone Timezone string, eg. 'Europe/London'
     * @param string $currency Currency, eg. 'EUR'
     * @param string $group Website group identifier
     * @param string $startDate Date at which the statistics for this website will start. Defaults to today's date in YYYY-MM-DD format
     * @param string $excludedUserAgents
     * @param string $keepURLFragments If 1, URL fragments will be kept when tracking. If 2, they
     *                                 will be removed. If 0, the default global behavior will be used.
     * @param string $settingValues JSON serialized settings eg {settingName: settingValue, ...}
     * @param string $type The website type, defaults to "website" if not set.
     * @param string $excludeUnknownUrls Track only URL matching one of website URLs
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
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
        $settingValues = '',
        $type = '',
        $excludeUnknownUrls = '',
        array $optional = []
    )
    {
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
            'settingValues' => $settingValues,
            'type' => $type,
            'excludeUnknownUrls' => $excludeUnknownUrls,
        ], $optional);
    }

    /**
     * Delete current site
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function deleteSite(array $optional = [])
    {
        return $this->_request('SitesManager.deleteSite', [], $optional);
    }

    /**
     * Add alias urls for the current site
     *
     * @param array $urls
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function addSiteAliasUrls($urls, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setSiteAliasUrls($urls, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getIpsForRange($ipRange, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setExcludedIps($excludedIps, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSearchKeywordParametersGlobal(array $optional = [])
    {
        return $this->_request('SitesManager.getSearchKeywordParametersGlobal', [], $optional);
    }

    /**
     * Get search categories
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getSearchCategoryParametersGlobal(array $optional = [])
    {
        return $this->_request('SitesManager.getSearchCategoryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded query parameters
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExcludedParameters(array $optional = [])
    {
        return $this->_request('SitesManager.getExcludedQueryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded user agents
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getExcludedUserAgentsGlobal(array $optional = [])
    {
        return $this->_request('SitesManager.getExcludedUserAgentsGlobal', [], $optional);
    }

    /**
     * Set the global excluded user agents
     *
     * @param array $excludedUserAgents
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setGlobalExcludedUserAgents($excludedUserAgents, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function isSiteSpecificUserAgentExcludeEnabled(array $optional = [])
    {
        return $this->_request('SitesManager.isSiteSpecificUserAgentExcludeEnabled', [], $optional);
    }

    /**
     * Set site specific user agent exclude
     *
     * @param int $enabled
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setSiteSpecificUserAgentExcludeEnabled($enabled, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getKeepURLFragmentsGlobal(array $optional = [])
    {
        return $this->_request('SitesManager.getKeepURLFragmentsGlobal', [], $optional);
    }

    /**
     * Set the url fragments global
     *
     * @param int $enabled
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setKeepURLFragmentsGlobal($enabled, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setExcludedParameters($excludedQueryParameters, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getExcludedIps(array $optional = [])
    {
        return $this->_request('SitesManager.getExcludedIpsGlobal', [], $optional);
    }

    /**
     * Get the default currency
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getDefaultCurrency(array $optional = [])
    {
        return $this->_request('SitesManager.getDefaultCurrency', [], $optional);
    }

    /**
     * Set the default currency
     *
     * @param string $defaultCurrency
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setDefaultCurrency($defaultCurrency, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getDefaultTimezone(array $optional = [])
    {
        return $this->_request('SitesManager.getDefaultTimezone', [], $optional);
    }

    /**
     * Set the default timezone
     *
     * @param string $defaultTimezone
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setDefaultTimezone($defaultTimezone, array $optional = [])
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
     * @throws InvalidRequestException
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
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function getCurrencyList(array $optional = [])
    {
        return $this->_request('SitesManager.getCurrencyList', [], $optional);
    }

    /**
     * Get a list with all currency symbols
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getCurrencySymbols(array $optional = [])
    {
        return $this->_request('SitesManager.getCurrencySymbols', [], $optional);
    }

    /**
     * Get a list with available timezones
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getTimezonesList(array $optional = [])
    {
        return $this->_request('SitesManager.getTimezonesList', [], $optional);
    }

    /**
     * Unknown
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getUniqueSiteTimezones(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function renameGroup($oldGroupName, $newGroupName, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getPatternMatchSites($pattern, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getTransitionsForPageTitle($pageTitle, $segment = '', $limitBeforeGrouping = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getTransitionsForPageUrl($pageUrl, $segment = '', $limitBeforeGrouping = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getTransitionsForAction(
        $actionName,
        $actionType,
        $segment = '',
        $limitBeforeGrouping = '',
        $parts = 'all',
        $returnNormalizedUrls = '',
        array $optional = []
    )
    {
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
     * @throws InvalidRequestException
     */
    public function getTransitionsTranslations(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCountry($segment = '', array $optional = [])
    {
        return $this->_request('UserCountry.getCountry', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get a list of used country codes to country names
     *
     * @return object
     * @throws InvalidRequestException
     */
    public function getCountryCodeMapping()
    {
        return $this->_request('UserCountry.getCountryCodeMapping');
    }

    /**
     * Get continents of all visitors
     *
     * @param string $segment
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getContinent($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getRegion($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCity($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getLocationFromIP($ip, $provider = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getCountryNumber($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getResolution($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getConfiguration($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserPlugin($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserLanguage($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserLanguageCode($segment = '', array $optional = [])
    {
        return $this->_request('UserLanguage.getLanguageCode', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * MODULE: USER MANAGER
     * Manage matomo users
     */

    /**
     * Set user preference
     *
     * @param string $userLogin Username
     * @param string $preferenceName
     * @param string $preferenceValue
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function setUserPreference($userLogin, $preferenceName, $preferenceValue, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserPreference($userLogin, $preferenceName, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUsers($userLogins = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUsersLogin(array $optional = [])
    {
        return $this->_request('UsersManager.getUsersLogin', [], $optional);
    }

    /**
     * Get sites by user access
     *
     * @param string $access
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getUsersSitesFromAccess($access, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUsersAccess(array $optional = [])
    {
        return $this->_request('UsersManager.getUsersAccessFromSite', [], $optional);
    }

    /**
     * Get all users with access $access to the current site
     *
     * @param string $access
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getUsersWithSiteAccess($access, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSitesAccessFromUser($userLogin, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUser($userLogin, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserByEmail($userEmail, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function addUser($userLogin, $password, $email, $alias = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setSuperUserAccess($userLogin, $hasSuperUserAccess, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function hasSuperUserAccess(array $optional = [])
    {
        return $this->_request('UsersManager.hasSuperUserAccess', [], $optional);
    }

    /**
     * Get a list of users with super user access
     *
     * @param array $optional
     * @return bool|object
     * @throws InvalidRequestException
     */
    public function getUsersHavingSuperUserAccess(array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function updateUser($userLogin, $password = '', $email = '', $alias = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function deleteUser($userLogin, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function userExists($userLogin, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function userEmailExists($userEmail, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function setUserAccess($userLogin, $access, $idSites, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getTokenAuth($userLogin, $md5Password, array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitFrequency($segment = '', $columns = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitLocalTime($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitServerTime($segment = '', $hideFutureHoursWhenToday = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getByDayOfWeek($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfVisitsPerDuration($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfVisitsPerPage($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfVisitsByDaySinceLast($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getNumberOfVisitsByCount($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitsSummary($segment = '', $columns = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisits($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUniqueVisitors($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getUserVisitors($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getActions($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getMaxActions($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getBounceCount($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getVisitsConverted($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSumVisitsLength($segment = '', array $optional = [])
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
     * @throws InvalidRequestException
     */
    public function getSumVisitsLengthPretty($segment = '', array $optional = [])
    {
        return $this->_request('VisitsSummary.getSumVisitsLengthPretty', [
            'segment' => $segment,
        ], $optional);
    }
}
