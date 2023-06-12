<?php

namespace VisualAppeal;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonException;

/**
 * Repository: https://github.com/VisualAppeal/Matomo-PHP-API
 * Official api reference: https://developer.matomo.org/api-reference/reporting-api
 */
class Matomo
{
    public const PERIOD_DAY = 'day';
    public const PERIOD_WEEK = 'week';
    public const PERIOD_MONTH = 'month';
    public const PERIOD_YEAR = 'year';
    public const PERIOD_RANGE = 'range';

    public const DATE_TODAY = 'today';
    public const DATE_YESTERDAY = 'yesterday';

    public const FORMAT_XML = 'xml';
    public const FORMAT_JSON = 'json';
    public const FORMAT_CSV = 'csv';
    public const FORMAT_TSV = 'tsv';
    public const FORMAT_HTML = 'html';
    public const FORMAT_RSS = 'rss';
    public const FORMAT_PHP = 'php';
    public const FORMAT_ORIGINAL = 'original';

    /**
     * @var string URL of the matomo installation
     */
    private string $_site;

    /**
     * @var string API Access token
     */
    private string $_token;

    /**
     * @var mixed The integer id of your website.
     */
    private mixed $_siteId;

    /**
     * @var string The period you request the statistics for.
     */
    private string $_period;

    /**
     * @var string|null
     */
    private ?string $_date = null;

    /**
     * @var string Defines the format of the output.
     */
    private string $_format;

    /**
     * @var int Defines the number of rows to be returned (-1: All rows).
     */
    private int $_filter_limit = 100;

    /**
     * @var string Returns data strings that can be internationalized and will be translated.
     */
    private string $_language = 'en';

    /**
     * @var string|null
     */
    private ?string $_rangeStart;

    /**
     * @var string|null
     */
    private ?string $_rangeEnd;

    /**
     * @var bool
     */
    private bool $_isJsonDecodeAssoc = false;

    /**
     * @var bool If the certificate of the matomo installation should be verified.
     */
    private bool $_verifySsl = false;

    /**
     * @var int How many redirects curl should execute until aborting.
     */
    private int $_maxRedirects = 5;

    /**
     * @var int Timeout in seconds.
     */
    private int $_timeout = 5;

    /**
     * @var Client|null Guzzle client
     */
    private ?Client $_client = null;

    /**
     * Create a new instance.
     *
     * @param  string  $site  URL of the matomo installation
     * @param  string  $token  API Access token
     * @param  int|null  $siteId  ID of the site
     * @param  string  $format
     * @param  string  $period
     * @param  string  $date
     * @param  string  $rangeStart
     * @param  string|null  $rangeEnd
     * @param  Client|null  $client
     */
    public function __construct(
        string $site,
        string $token,
        int $siteId = null,
        string $format = self::FORMAT_JSON,
        string $period = self::PERIOD_DAY,
        string $date = self::DATE_YESTERDAY,
        string $rangeStart = '',
        string $rangeEnd = null,
        Client $client = null,
    ) {
        $this->_site       = $site;
        $this->_token      = $token;
        $this->_siteId     = $siteId;
        $this->_format     = $format;
        $this->_period     = $period;
        $this->_rangeStart = $rangeStart;
        $this->_rangeEnd   = $rangeEnd;

        if ( ! empty($rangeStart)) {
            $this->setRange($rangeStart, $rangeEnd);
        } else {
            $this->setDate($date);
        }

        if ($client !== null) {
            $this->setClient($client);
        } else {
            $this->setClient(new Client());
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
     * @param  string  $url
     *
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
     * @param  string  $token
     *
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
    public function getSiteId(): mixed
    {
        return $this->_siteId;
    }

    /**
     * Set current site ID
     *
     * @param  mixed|null  $id
     *
     * @return $this
     */
    public function setSiteId(mixed $id = null): Matomo
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
     * @param  string  $format
     *        FORMAT_XML
     *        FORMAT_JSON
     *        FORMAT_CSV
     *        FORMAT_TSV
     *        FORMAT_HTML
     *        FORMAT_RSS
     *        FORMAT_PHP
     *
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
     * @param  string  $language
     *
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
     * @param  string|null  $date  Format Y-m-d or class constant:
     *        DATE_TODAY
     *        DATE_YESTERDAY
     *
     * @return $this
     */
    public function setDate(string $date = null): Matomo
    {
        $this->_date       = $date;
        $this->_rangeStart = null;
        $this->_rangeEnd   = null;

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
     * @param  string  $period
     *        PERIOD_DAY
     *        PERIOD_MONTH
     *        PERIOD_WEEK
     *        PERIOD_YEAR
     *        PERIOD_RANGE
     *
     * @return $this
     */
    public function setPeriod(string $period): Matomo
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
        }

        return $this->_rangeStart.','.$this->_rangeEnd;
    }

    /**
     * Set date range
     *
     * @param  string|null  $rangeStart  e.g. 2012-02-10 (YYYY-mm-dd) or last5(lastX), previous12(previousY)...
     * @param  string|null  $rangeEnd  e.g. 2012-02-12. Leave this parameter empty to request all data from
     *                         $rangeStart until now
     *
     * @return $this
     */
    public function setRange(string $rangeStart = null, string $rangeEnd = null): Matomo
    {
        $this->_date       = '';
        $this->_rangeStart = $rangeStart;
        $this->_rangeEnd   = $rangeEnd;

        if (is_null($rangeEnd)) {
            if (str_contains($rangeStart ?? '', 'last')
                || str_contains($rangeStart ?? '', 'previous')
            ) {
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
     * @param  int  $filterLimit
     *
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
     * @param  bool  $isJsonDecodeAssoc  false decode as Object, true for decode as Associate array
     *
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
     * @param  bool  $verifySsl
     *
     * @return Matomo
     */
    public function setVerifySsl(bool $verifySsl): Matomo
    {
        $this->_verifySsl = $verifySsl;

        return $this;
    }

    /**
     * How many redirects curl should execute until aborting.
     *
     * @return int
     */
    public function getMaxRedirects(): int
    {
        return $this->_maxRedirects;
    }

    /**
     * Set how many redirects curl should execute until aborting.
     *
     * @param  int  $maxRedirects
     *
     * @return Matomo
     */
    public function setMaxRedirects(int $maxRedirects): Matomo
    {
        $this->_maxRedirects = $maxRedirects;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->_timeout;
    }

    /**
     * @param  int  $timeout
     *
     * @return Matomo
     */
    public function setTimeout(int $timeout): Matomo
    {
        $this->_timeout = $timeout;

        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->_client;
    }

    /**
     * @param  Client|null  $client
     */
    public function setClient(?Client $client): void
    {
        $this->_client = $client;
    }

    /**
     * Reset all default variables.
     */
    public function reset(): Matomo
    {
        $this->_period     = self::PERIOD_DAY;
        $this->_date       = '';
        $this->_rangeStart = 'yesterday';
        $this->_rangeEnd   = null;

        return $this;
    }

    /**
     * Requests to matomo api
     */

    /**
     * Make API request
     *
     * @param  string  $method  HTTP method
     * @param  array  $params  Query parameters
     * @param  array  $optional  Optional arguments for this api call
     * @param  string|null  $overrideFormat  Override the response format
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     * @throws InvalidResponseException
     */
    private function _request(
        string $method,
        array $params = [],
        array $optional = [],
        string $overrideFormat = null
    ): mixed {
        $url = $this->_parseUrl($method, $params + $optional);
        if ($url === '') {
            throw new InvalidRequestException('Could not parse URL!');
        }

        $format = $overrideFormat ?? $this->_format;

        try {
            $response = $this->_client->get($url, [
                'verify'          => $this->_verifySsl,
                'allow_redirects' => [
                    'max' => $this->_maxRedirects,
                ],
            ]);
        } catch (GuzzleException $e) {
            // Network error, e.g. timeout or connection refused
            throw new InvalidRequestException($e->getMessage(), $e->getCode(), $e);
        }

        $contents = $response->getBody()->getContents();

        // Validate if the response was successful
        if ($response->getStatusCode() !== 200) {
            throw new InvalidRequestException($contents,
                $response->getStatusCode());
        }

        // Sometimes the response was unsuccessful, but the status code was 200
        if ($format === self::FORMAT_JSON) {
            $valid = $this->_isValidResponse($contents);

            if ($valid !== true) {
                throw new InvalidResponseException($valid.' ('.$this->_parseUrl($method, $params)
                                                   .')', 403);
            }
        }

        return $this->_parseResponse($contents, $format);
    }

    /**
     * Create request url with parameters
     *
     * @param  string  $method  The request method
     * @param  array  $params  Request params
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function _parseUrl(string $method, array $params = []): string
    {
        $params = [
                      'module'       => 'API',
                      'method'       => $method,
                      'token_auth'   => $this->_token,
                      'idSite'       => $this->_siteId,
                      'period'       => $this->_period,
                      'format'       => $this->_format,
                      'language'     => $this->_language,
                      'filter_limit' => $this->_filter_limit
                  ] + $params;

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = urlencode(implode(',', $value));
            } else {
                $params[$key] = urlencode($value ?? '');
            }
        }

        if ( ! empty($this->_rangeStart) && ! empty($this->_rangeEnd)) {
            $params += [
                'date' => $this->_rangeStart.','.$this->_rangeEnd,
            ];
        } elseif ( ! empty($this->_date)) {
            $params += [
                'date' => $this->_date,
            ];
        } else {
            throw new InvalidArgumentException('Specify a date or a date range!');
        }

        $url = $this->_site;

        $i = 0;
        foreach ($params as $param => $val) {
            if ( ! empty($val)) {
                $i++;
                if ($i > 1) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }

                if (is_array($val)) {
                    $val = implode(',', $val);
                }
                $url .= $param.'='.$val;
            }
        }

        return $url;
    }

    /**
     * Check if the request was successful.
     *
     * @param  string  $contents
     *
     * @return bool|string
     * @throws JsonException
     */
    private function _isValidResponse(string $contents): bool|string
    {
        if ($contents === '') {
            return 'Empty response!';
        }

        $result = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (isset($result['result']) && (strtolower($result['result']) === 'error')) {
            return $result['message'];
        }

        return true;
    }

    /**
     * Parse request result
     *
     * @param  string  $contents
     * @param  string|null  $overrideFormat  Override the default format
     *
     * @return mixed Either the parsed response body object (parsed from json) or the raw response object.
     * @throws JsonException
     */
    private function _parseResponse(string $contents, string $overrideFormat = null): mixed
    {
        $format = $overrideFormat ?? $this->_format;

        return match ($format) {
            self::FORMAT_JSON => json_decode($contents,
                $this->_isJsonDecodeAssoc, 512,
                JSON_THROW_ON_ERROR),
            default => $contents,
        };
    }

    /**
     * MODULE: API
     * API metadata
     */

    /**
     * Get current matomo version
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException|InvalidResponseException
     */
    public function getMatomoVersion(array $optional = []): mixed
    {
        return $this->_request('API.getMatomoVersion', [], $optional);
    }

    /**
     * Get current ip address (from the server executing this script)
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getIpFromHeader(array $optional = []): mixed
    {
        return $this->_request('API.getIpFromHeader', [], $optional);
    }

    /**
     * Get current settings
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSettings(array $optional = []): mixed
    {
        return $this->_request('API.getSettings', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultMetricTranslations(array $optional = []): mixed
    {
        return $this->_request('API.getDefaultMetricTranslations', [], $optional);
    }

    /**
     * Get default metrics
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultMetrics(array $optional = []): mixed
    {
        return $this->_request('API.getDefaultMetrics', [], $optional);
    }

    /**
     * Get default processed metrics
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultProcessedMetrics(array $optional = []): mixed
    {
        return $this->_request('API.getDefaultProcessedMetrics', [], $optional);
    }

    /**
     * Get default metrics documentation
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultMetricsDocumentation(array $optional = []): mixed
    {
        return $this->_request('API.getDefaultMetricsDocumentation', [], $optional);
    }

    /**
     * Get default metric translations
     *
     * @param  array  $sites  Array with the ID's of the sites
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSegmentsMetadata(array $sites = [], array $optional = []): mixed
    {
        return $this->_request('API.getSegmentsMetadata', [
            'idSites' => $sites
        ], $optional);
    }

    /**
     * Get the url of the logo
     *
     * @param  bool  $pathOnly  Return the url (false) or the absolute path (true)
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getLogoUrl(bool $pathOnly = false, array $optional = []): mixed
    {
        return $this->_request('API.getLogoUrl', [
            'pathOnly' => $pathOnly
        ], $optional);
    }

    /**
     * Get the url of the header logo
     *
     * @param  bool  $pathOnly  Return the url (false) or the absolute path (true)
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getHeaderLogoUrl(bool $pathOnly = false, array $optional = []): mixed
    {
        return $this->_request('API.getHeaderLogoUrl', [
            'pathOnly' => $pathOnly
        ], $optional);
    }

    /**
     * Get metadata from the API
     *
     * @param  string  $apiModule  Module
     * @param  string  $apiAction  Action
     * @param  array  $apiParameters  Parameters
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMetadata(
        string $apiModule,
        string $apiAction,
        array $apiParameters = [],
        array $optional = []
    ): mixed {
        return $this->_request('API.getMetadata', [
            'apiModule'     => $apiModule,
            'apiAction'     => $apiAction,
            'apiParameters' => $apiParameters,
        ], $optional);
    }

    /**
     * Get metadata from a report
     *
     * @param  array  $idSites  Array with the ID's of the sites
     * @param  string  $hideMetricsDoc
     * @param  string  $showSubtableReports
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getReportMetadata(
        array $idSites,
        string $hideMetricsDoc = '',
        string $showSubtableReports = '',
        array $optional = []
    ): mixed {
        return $this->_request('API.getReportMetadata', [
            'idSites'             => $idSites,
            'hideMetricsDoc'      => $hideMetricsDoc,
            'showSubtableReports' => $showSubtableReports,
        ], $optional);
    }

    /**
     * Get processed report
     *
     * @param  string  $apiModule  Module
     * @param  string  $apiAction  Action
     * @param  string  $segment
     * @param  string  $apiParameters
     * @param  int|string  $idGoal
     * @param  bool|string  $showTimer
     * @param  string  $hideMetricsDoc
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getProcessedReport(
        string $apiModule,
        string $apiAction,
        string $segment = '',
        string $apiParameters = '',
        int|string $idGoal = '',
        bool|string $showTimer = '1',
        string $hideMetricsDoc = '',
        array $optional = []
    ): mixed {
        return $this->_request('API.getProcessedReport', [
            'apiModule'      => $apiModule,
            'apiAction'      => $apiAction,
            'segment'        => $segment,
            'apiParameters'  => $apiParameters,
            'idGoal'         => $idGoal,
            'showTimer'      => $showTimer,
            'hideMetricsDoc' => $hideMetricsDoc,
        ], $optional);
    }

    /**
     * Get Api
     *
     * @param  string  $segment
     * @param  string  $columns
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getApi(
        string $segment = '',
        string $columns = '',
        array $optional = []
    ): mixed {
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
     * @param  string  $segment
     * @param  string  $column
     * @param  string  $idGoal
     * @param  string  $legendAppendMetric
     * @param  string  $labelUseAbsoluteUrl
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getRowEvolution(
        $apiModule,
        $apiAction,
        string $segment = '',
        string $column = '',
        string $idGoal = '',
        string $legendAppendMetric = '1',
        string $labelUseAbsoluteUrl = '1',
        array $optional = []
    ): mixed {
        return $this->_request('API.getRowEvolution', [
            'apiModule'           => $apiModule,
            'apiAction'           => $apiAction,
            'segment'             => $segment,
            'column'              => $column,
            'idGoal'              => $idGoal,
            'legendAppendMetric'  => $legendAppendMetric,
            'labelUseAbsoluteUrl' => $labelUseAbsoluteUrl,
        ], $optional);
    }

    /**
     * Get the result of multiple requests bundled together
     * Take as an argument an array of the API methods to send together
     * For example, ['API.get', 'Action.get', 'DeviceDetection.getType']
     *
     * @param  array  $methods
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getBulkRequest(array $methods = [], array $optional = []): mixed
    {
        $urls = [];

        foreach ($methods as $key => $method) {
            $urls['urls['.$key.']'] = urlencode('method='.$method);
        }

        return $this->_request('API.getBulkRequest', $urls, $optional);
    }

    /**
     * Get a list of available widgets.
     *
     * @return object|bool
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getWidgetMetadata(): mixed
    {
        return $this->_request('API.getWidgetMetadata');
    }

    /**
     * Get a list of all available pages that exist including the widgets they include.
     *
     * @return object|bool
     * @throws InvalidRequestException
     * @throws JsonException|InvalidResponseException
     */
    public function getReportPagesMetadata(): mixed
    {
        return $this->_request('API.getReportPagesMetadata');
    }

    /**
     * Get suggested values for segments
     *
     * @param  string  $segmentName
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSuggestedValuesForSegment(
        string $segmentName,
        array $optional = []
    ): mixed {
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
     * @param  string  $segment
     * @param  string  $columns
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAction(
        string $segment = '',
        string $columns = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get page urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageUrls(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page URLs after a site search
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageUrlsFollowingSiteSearch(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getPageUrlsFollowingSiteSearch', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles after a site search
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageTitlesFollowingSiteSearch(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getPageTitlesFollowingSiteSearch', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get entry page urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getEntryPageUrls(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getEntryPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get exit page urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExitPageUrls(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getExitPageUrls', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page url information
     *
     * @param  string  $pageUrl  The page url
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageUrl(
        string $pageUrl,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getPageUrl', [
            'pageUrl' => $pageUrl,
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageTitles(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get entry page urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getEntryPageTitles(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getEntryPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get exit page urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExitPageTitles(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getExitPageTitles', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get page titles
     *
     * @param  string  $pageName  The page name
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPageTitle(
        string $pageName,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getPageTitle', [
            'pageName' => $pageName,
            'segment'  => $segment,
        ], $optional);
    }

    /**
     * Get downloads
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDownloads(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getDownloads', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get download information
     *
     * @param  string  $downloadUrl  URL of the download
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDownload(
        string $downloadUrl,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getDownload', [
            'downloadUrl' => $downloadUrl,
            'segment'     => $segment,
        ], $optional);
    }

    /**
     * Get outlinks
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOutlinks(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getOutlinks', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get outlink information
     *
     * @param  string  $outlinkUrl  URL of the outlink
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOutlink(
        string $outlinkUrl,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getOutlink', [
            'outlinkUrl' => $outlinkUrl,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get the site search keywords
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSiteSearchKeywords(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Actions.getSiteSearchKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get search keywords with no search results
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSiteSearchNoResultKeywords(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Actions.getSiteSearchNoResultKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get site search categories
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSiteSearchCategories(string $segment = '', array $optional = []): mixed
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
     * @param  string  $note
     * @param  int  $starred
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addAnnotation(string $note, int $starred = 0, array $optional = []): mixed
    {
        return $this->_request('Annotations.add', [
            'note'    => $note,
            'starred' => $starred,
        ], $optional);
    }

    /**
     * Save annotation
     *
     * @param  int  $idNote
     * @param  string  $note
     * @param  string  $starred
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function saveAnnotation(
        int $idNote,
        string $note = '',
        string $starred = '',
        array $optional = []
    ): mixed {
        return $this->_request('Annotations.save', [
            'idNote'  => $idNote,
            'note'    => $note,
            'starred' => $starred,
        ], $optional);
    }

    /**
     * Delete annotation
     *
     * @param  int  $idNote
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteAnnotation(int $idNote, array $optional = []): mixed
    {
        return $this->_request('Annotations.delete', [
            'idNote' => $idNote,
        ], $optional);
    }

    /**
     * Delete all annotations
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteAllAnnotations(array $optional = []): mixed
    {
        return $this->_request('Annotations.deleteAll', [], $optional);
    }

    /**
     * Get annotation
     *
     * @param  int  $idNote
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAnnotation(int $idNote, array $optional = []): mixed
    {
        return $this->_request('Annotations.get', [
            'idNote' => $idNote,
        ], $optional);
    }

    /**
     * Get all annotations
     *
     * @param  string  $lastN
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAllAnnotation(string $lastN = '', array $optional = []): mixed
    {
        return $this->_request('Annotations.getAll', [
            'lastN' => $lastN,
        ], $optional);
    }

    /**
     * Get number of annotation for current period
     *
     * @param  int  $lastN
     * @param  string  $getAnnotationText
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAnnotationCountForDates(
        int $lastN,
        string $getAnnotationText,
        array $optional = []
    ): mixed {
        return $this->_request('Annotations.getAnnotationCountForDates', [
            'lastN'             => $lastN,
            'getAnnotationText' => $getAnnotationText
        ], $optional);
    }

    /**
     * MODULE: CONTENTS
     */

    /**
     * Get content names
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getContentNames(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Contents.getContentNames', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get content pieces
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getContentPieces(string $segment = '', array $optional = []): mixed
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
     * @param  int  $idAlert
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAlert(int $idAlert, array $optional = []): mixed
    {
        return $this->_request('CustomAlerts.getAlert', [
            'idAlert' => $idAlert,
        ], $optional);
    }

    /**
     * Get values for alerts in the past
     *
     * @param  int  $idAlert
     * @param  string  $subPeriodN
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getValuesForAlertInPast(
        int $idAlert,
        string $subPeriodN,
        array $optional = []
    ): mixed {
        return $this->_request('CustomAlerts.getValuesForAlertInPast', [
            'idAlert'    => $idAlert,
            'subPeriodN' => $subPeriodN,
        ], $optional);
    }

    /**
     * Get all alert details
     *
     * @param  string  $idSites  Comma separated list of site IDs
     * @param  string  $ifSuperUserReturnAllAlerts
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAlerts(
        string $idSites,
        string $ifSuperUserReturnAllAlerts = '',
        array $optional = []
    ): mixed {
        return $this->_request('CustomAlerts.getAlerts', [
            'idSites'                    => $idSites,
            'ifSuperUserReturnAllAlerts' => $ifSuperUserReturnAllAlerts,
        ], $optional);
    }

    /**
     * Add alert
     *
     * @param  string  $name
     * @param  array  $idSites  Array of site IDs
     * @param  int  $emailMe
     * @param  string  $additionalEmails
     * @param  string  $phoneNumbers
     * @param  string  $metric
     * @param  string  $metricCondition
     * @param  string  $metricValue
     * @param  string  $comparedTo
     * @param  string  $reportUniqueId
     * @param  string  $reportCondition
     * @param  string  $reportValue
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addAlert(
        string $name,
        array $idSites,
        int $emailMe,
        string $additionalEmails,
        string $phoneNumbers,
        string $metric,
        string $metricCondition,
        string $metricValue,
        string $comparedTo,
        string $reportUniqueId,
        string $reportCondition = '',
        string $reportValue = '',
        array $optional = []
    ): mixed {
        return $this->_request('CustomAlerts.addAlert', [
            'name'             => $name,
            'idSites'          => $idSites,
            'emailMe'          => $emailMe,
            'additionalEmails' => $additionalEmails,
            'phoneNumbers'     => $phoneNumbers,
            'metric'           => $metric,
            'metricCondition'  => $metricCondition,
            'metricValue'      => $metricValue,
            'comparedTo'       => $comparedTo,
            'reportUniqueId'   => $reportUniqueId,
            'reportCondition'  => $reportCondition,
            'reportValue'      => $reportValue,
        ], $optional);
    }

    /**
     * Edit alert
     *
     * @param  int  $idAlert
     * @param  string  $name
     * @param  array  $idSites  Array of site IDs
     * @param  int  $emailMe
     * @param  string  $additionalEmails
     * @param  string  $phoneNumbers
     * @param  string  $metric
     * @param  string  $metricCondition
     * @param  string  $metricValue
     * @param  string  $comparedTo
     * @param  string  $reportUniqueId
     * @param  string  $reportCondition
     * @param  string  $reportValue
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function editAlert(
        int $idAlert,
        string $name,
        array $idSites,
        int $emailMe,
        string $additionalEmails,
        string $phoneNumbers,
        string $metric,
        string $metricCondition,
        string $metricValue,
        string $comparedTo,
        string $reportUniqueId,
        string $reportCondition = '',
        string $reportValue = '',
        array $optional = []
    ): mixed {
        return $this->_request('CustomAlerts.editAlert', [
            'idAlert'          => $idAlert,
            'name'             => $name,
            'idSites'          => $idSites,
            'emailMe'          => $emailMe,
            'additionalEmails' => $additionalEmails,
            'phoneNumbers'     => $phoneNumbers,
            'metric'           => $metric,
            'metricCondition'  => $metricCondition,
            'metricValue'      => $metricValue,
            'comparedTo'       => $comparedTo,
            'reportUniqueId'   => $reportUniqueId,
            'reportCondition'  => $reportCondition,
            'reportValue'      => $reportValue,
        ], $optional);
    }

    /**
     * Delete Alert
     *
     * @param  int  $idAlert
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteAlert(int $idAlert, array $optional = []): mixed
    {
        return $this->_request('CustomAlerts.deleteAlert', [
            'idAlert' => $idAlert,
        ], $optional);
    }

    /**
     * Get triggered alerts
     *
     * @param  string  $idSites
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTriggeredAlerts(string $idSites, array $optional = []): mixed
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
     * @param  int  $idDimension
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCustomDimension(int $idDimension, array $optional = []): mixed
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
     * @param  string  $name  The name of the dimension
     * @param  string  $scope  Either 'visit' or 'action'. To get an up-to-date list of available scopes fetch the
     *                      API method `CustomDimensions.getAvailableScopes`
     * @param  int  $active  '0' if dimension should be inactive, '1' if dimension should be active
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function configureNewCustomDimension(
        string $name,
        string $scope,
        int $active,
        array $optional = []
    ): mixed {
        return $this->_request('CustomDimensions.configureNewCustomDimension', [
            'name'   => $name,
            'scope'  => $scope,
            'active' => $active,
        ], $optional);
    }

    /**
     * Updates an existing Custom Dimension. This method updates all values, you need to pass existing values of the
     * dimension if you do not want to reset any value. Requires at least Admin access for the specified website.
     *
     * @param  int  $idDimension  The id of a Custom Dimension.
     * @param  string  $name  The name of the dimension
     * @param  int  $active  '0' if dimension should be inactive, '1' if dimension should be active
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function configureExistingCustomDimension(
        int $idDimension,
        string $name,
        int $active,
        array $optional = []
    ): mixed {
        return $this->_request('CustomDimensions.configureExistingCustomDimension', [
            'idDimension' => $idDimension,
            'name'        => $name,
            'active'      => $active,
        ], $optional);
    }

    /**
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getConfiguredCustomDimensions(): mixed
    {
        return $this->_request('CustomDimensions.getConfiguredCustomDimensions');
    }

    /**
     * Get a list of all supported scopes that can be used in the API method
     * `CustomDimensions.configureNewCustomDimension`. The response also contains information whether more Custom
     * Dimensions can be created or not. Requires at least Admin access for the specified website.
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAvailableScopes(): mixed
    {
        return $this->_request('CustomDimensions.getAvailableScopes');
    }

    /**
     * Get a list of all available dimensions that can be used in an extraction. Requires at least Admin access
     * to one website.
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAvailableExtractionDimensions(): mixed
    {
        return $this->_request('CustomDimensions.getAvailableExtractionDimensions');
    }

    /**
     * MODULE: CUSTOM VARIABLES
     * Custom variable information
     */

    /**
     * Get custom variables
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return object|bool|array
     * @throws InvalidRequestException
     * @throws JsonException|InvalidResponseException
     */
    public function getCustomVariables(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('CustomVariables.getCustomVariables', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get information about a custom variable
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCustomVariable(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('CustomVariables.getCustomVariablesValuesFromNameId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * MODULE: Dashboard
     */

    /**
     * Get list of dashboards
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDashboards(array $optional = []): mixed
    {
        return $this->_request('Dashboard.getDashboards', [], $optional);
    }

    /**
     * MODULE: DEVICES DETECTION
     */

    /**
     * Get Device Type.
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDeviceType(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getType', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get Device Brand.
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDeviceBrand(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getBrand', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get Device Model.
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDeviceModel(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getModel', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get operating system families
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOSFamilies(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getOsFamilies', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get os versions
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOsVersions(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getOsVersions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browsers
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getBrowsers(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getBrowsers', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browser versions
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getBrowserVersions(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('DevicesDetection.getBrowserVersions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get browser engines
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getBrowserEngines(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  string  $secondaryDimension  ('eventAction' or 'eventName')
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getEventCategory(
        string $segment = '',
        string $secondaryDimension = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getCategory', [
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get event actions
     *
     * @param  string  $segment
     * @param  string  $secondaryDimension  ('eventName' or 'eventCategory')
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getEventAction(
        string $segment = '',
        string $secondaryDimension = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getAction', [
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get event names
     *
     * @param  string  $segment
     * @param  string  $secondaryDimension  ('eventAction' or 'eventCategory')
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getEventName(
        string $segment = '',
        string $secondaryDimension = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getName', [
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ], $optional);
    }

    /**
     * Get action from category ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getActionFromCategoryId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getActionFromCategoryId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get name from category ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNameFromCategoryId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getNameFromCategoryId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get category from action ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCategoryFromActionId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getCategoryFromActionId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get name from action ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNameFromActionId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getNameFromActionId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get action from name ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getActionFromNameId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getActionFromNameId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get category from name ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCategoryFromNameId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Events.getCategoryFromNameId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * MODULE: EXAMPLE API
     * Get api and matomo information
     */

    /**
     * Get the matomo version
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleMatomoVersion(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getMatomoVersion', [], $optional);
    }

    /**
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleAnswerToLife(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getAnswerToLife', [], $optional);
    }

    /**
     * Unknown
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleObject(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getObject', [], $optional);
    }

    /**
     * Get the sum of the parameters
     *
     * @param  int  $a
     * @param  int  $b
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleSum(int $a = 0, int $b = 0, array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getSum', [
            'a' => $a,
            'b' => $b,
        ], $optional);
    }

    /**
     * Returns nothing but the success of the request
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleNull(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getNull', [], $optional);
    }

    /**
     * Get a short matomo description
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleDescriptionArray(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getDescriptionArray', [], $optional);
    }

    /**
     * Get a short comparison with other analytic software
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleCompetitionDatatable(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getCompetitionDatatable', [], $optional);
    }

    /**
     * Get information about 42
     * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleMoreInformationAnswerToLife(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getMoreInformationAnswerToLife', [], $optional);
    }

    /**
     * Get a multidimensional array
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExampleMultiArray(array $optional = []): mixed
    {
        return $this->_request('ExampleAPI.getMultiArray', [], $optional);
    }

    /**
     * MODULE: EXAMPLE PLUGIN
     */

    /**
     * Get a multidimensional array
     *
     * @param  int  $truth
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExamplePluginAnswerToLife(int $truth = 1, array $optional = []): mixed
    {
        return $this->_request('ExamplePlugin.getAnswerToLife', [
            'truth' => $truth,
        ], $optional);
    }

    /**
     * Get a multidimensional array
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExamplePluginReport(string $segment = '', array $optional = []): mixed
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
     * @param  string  $featureName
     * @param  string  $like
     * @param  string  $message
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function sendFeedbackForFeature(
        string $featureName,
        string $like,
        string $message = '',
        array $optional = []
    ): mixed {
        return $this->_request('Feedback.sendFeedbackForFeature', [
            'featureName' => $featureName,
            'like'        => $like,
            'message'     => $message,
        ], $optional);
    }

    /**
     * MODULE: GOALS
     * Handle goals
     */

    /**
     * Get all goals
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getGoals(array $optional = []): mixed
    {
        return $this->_request('Goals.getGoals', [], $optional);
    }

    /**
     * Add a goal
     *
     * @param  string  $name
     * @param  string  $matchAttribute
     * @param  string  $pattern
     * @param  string  $patternType
     * @param  string  $caseSensitive
     * @param  string  $revenue
     * @param  string  $allowMultipleConversionsPerVisit
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addGoal(
        string $name,
        string $matchAttribute,
        string $pattern,
        string $patternType,
        string $caseSensitive = '',
        string $revenue = '',
        string $allowMultipleConversionsPerVisit = '',
        array $optional = []
    ): mixed {
        return $this->_request('Goals.addGoal', [
            'name'                             => $name,
            'matchAttribute'                   => $matchAttribute,
            'pattern'                          => $pattern,
            'patternType'                      => $patternType,
            'caseSensitive'                    => $caseSensitive,
            'revenue'                          => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ], $optional);
    }

    /**
     * Update a goal
     *
     * @param  int  $idGoal
     * @param  string  $name
     * @param  string  $matchAttribute
     * @param  string  $pattern
     * @param  string  $patternType
     * @param  string  $caseSensitive
     * @param  string  $revenue
     * @param  string  $allowMultipleConversionsPerVisit
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function updateGoal(
        int $idGoal,
        string $name,
        string $matchAttribute,
        string $pattern,
        string $patternType,
        string $caseSensitive = '',
        string $revenue = '',
        string $allowMultipleConversionsPerVisit = '',
        array $optional = []
    ): mixed {
        return $this->_request('Goals.updateGoal', [
            'idGoal'                           => $idGoal,
            'name'                             => $name,
            'matchAttribute'                   => $matchAttribute,
            'pattern'                          => $pattern,
            'patternType'                      => $patternType,
            'caseSensitive'                    => $caseSensitive,
            'revenue'                          => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ], $optional);
    }

    /**
     * Delete a goal
     *
     * @param  int  $idGoal
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteGoal(int $idGoal, array $optional = []): mixed
    {
        return $this->_request('Goals.deleteGoal', [
            'idGoal' => $idGoal,
        ], $optional);
    }

    /**
     * Get the SKU of the items
     *
     * @param  string  $abandonedCarts
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getItemsSku(string $abandonedCarts, array $optional = []): mixed
    {
        return $this->_request('Goals.getItemsSku', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get the name of the items
     *
     * @param  bool  $abandonedCarts
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getItemsName(bool $abandonedCarts, array $optional = []): mixed
    {
        return $this->_request('Goals.getItemsName', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get the categories of the items
     *
     * @param  bool  $abandonedCarts
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getItemsCategory(bool $abandonedCarts, array $optional = []): mixed
    {
        return $this->_request('Goals.getItemsCategory', [
            'abandonedCarts' => $abandonedCarts,
        ], $optional);
    }

    /**
     * Get conversion rates from a goal
     *
     * @param  string  $segment
     * @param  string  $idGoal
     * @param  array  $columns
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getGoal(
        string $segment = '',
        string $idGoal = '',
        array $columns = [],
        array $optional = []
    ): mixed {
        return $this->_request('Goals.get', [
            'segment' => $segment,
            'idGoal'  => $idGoal,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get information about a time period, and it's conversion rates
     *
     * @param  string  $segment
     * @param  string  $idGoal
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDaysToConversion(
        string $segment = '',
        string $idGoal = '',
        array $optional = []
    ): mixed {
        return $this->_request('Goals.getDaysToConversion', [
            'segment' => $segment,
            'idGoal'  => $idGoal,
        ], $optional);
    }

    /**
     * Get information about how many site visits create a conversion
     *
     * @param  string  $segment
     * @param  string  $idGoal
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitsUntilConversion(
        string $segment = '',
        string $idGoal = '',
        array $optional = []
    ): mixed {
        return $this->_request('Goals.getVisitsUntilConversion', [
            'segment' => $segment,
            'idGoal'  => $idGoal,
        ], $optional);
    }

    /**
     * MODULE: IMAGE GRAPH
     * Generate png graphs
     */

    public const GRAPH_EVOLUTION = 'evolution';
    public const GRAPH_VERTICAL_BAR = 'verticalBar';
    public const GRAPH_PIE = 'pie';
    public const GRAPH_PIE_3D = '3dPie';

    /**
     * Generate a png report
     *
     * @param  string  $apiModule  Module
     * @param  string  $apiAction  Action
     * @param  string  $graphType  'evolution', 'verticalBar', 'pie' or '3dPie'
     * @param  string  $outputType
     * @param  string  $columns
     * @param  string  $labels
     * @param  string  $showLegend
     * @param  int|string  $width
     * @param  int|string  $height
     * @param  int|string  $fontSize
     * @param  string  $legendFontSize
     * @param  bool|string  $aliasedGraph  'By default, Graphs are "smooth" (anti-aliased). If you are
     *                              generating hundreds of graphs and are concerned with performance,
     *                              you can set aliasedGraph=0. This will disable anti aliasing and
     *                              graphs will be generated faster, but look less pretty.'
     * @param  string  $idGoal
     * @param  array  $colors  Use own colors instead of the default. The colors have to be in hexadecimal
     *                      value without '#'
     * @param  array  $optional
     *
     * @return bool|string
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getImageGraph(
        string $apiModule,
        string $apiAction,
        string $graphType = '',
        string $outputType = '0',
        string $columns = '',
        string $labels = '',
        string $showLegend = '1',
        int|string $width = '',
        int|string $height = '',
        int|string $fontSize = '9',
        string $legendFontSize = '',
        bool|string $aliasedGraph = '1',
        string $idGoal = '',
        array $colors = [],
        array $optional = []
    ): mixed {
        return $this->_request('ImageGraph.get', [
            'apiModule'      => $apiModule,
            'apiAction'      => $apiAction,
            'graphType'      => $graphType,
            'outputType'     => $outputType,
            'columns'        => $columns,
            'labels'         => $labels,
            'showLegend'     => $showLegend,
            'width'          => $width,
            'height'         => $height,
            'fontSize'       => $fontSize,
            'legendFontSize' => $legendFontSize,
            'aliasedGraph'   => $aliasedGraph,
            'idGoal '        => $idGoal,
            'colors'         => $colors,
        ], $optional, self::FORMAT_PHP);
    }

    /**
     * MODULE: LANGUAGES MANAGER
     * Get plugin insights
     */

    /**
     * Check if matomo can generate insights for current period
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function canGenerateInsights(array $optional = []): mixed
    {
        return $this->_request('Insights.canGenerateInsights', [], $optional);
    }

    /**
     * Get insights overview
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getInsightsOverview(string $segment, array $optional = []): mixed
    {
        return $this->_request('Insights.getInsightsOverview', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get movers and shakers overview
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMoversAndShakersOverview(string $segment, array $optional = []): mixed
    {
        return $this->_request('Insights.getMoversAndShakersOverview', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get movers and shakers
     *
     * @param  int  $reportUniqueId
     * @param  string  $segment
     * @param  int  $comparedToXPeriods
     * @param  int  $limitIncreaser
     * @param  int  $limitDecreaser
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMoversAndShakers(
        int $reportUniqueId,
        string $segment,
        int $comparedToXPeriods = 1,
        int $limitIncreaser = 4,
        int $limitDecreaser = 4,
        array $optional = []
    ): mixed {
        return $this->_request('Insights.getMoversAndShakers', [
            'reportUniqueId'     => $reportUniqueId,
            'segment'            => $segment,
            'comparedToXPeriods' => $comparedToXPeriods,
            'limitIncreaser'     => $limitIncreaser,
            'limitDecreaser'     => $limitDecreaser,
        ], $optional);
    }

    /**
     * Get insights
     *
     * @param  int  $reportUniqueId
     * @param  string  $segment
     * @param  int  $limitIncreaser
     * @param  int  $limitDecreaser
     * @param  string  $filterBy
     * @param  int  $minImpactPercent  (0-100)
     * @param  int  $minGrowthPercent  (0-100)
     * @param  int  $comparedToXPeriods
     * @param  string  $orderBy
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getInsights(
        int $reportUniqueId,
        string $segment,
        int $limitIncreaser = 5,
        int $limitDecreaser = 5,
        string $filterBy = '',
        int $minImpactPercent = 2,
        int $minGrowthPercent = 20,
        int $comparedToXPeriods = 1,
        string $orderBy = 'absolute',
        array $optional = []
    ): mixed {
        return $this->_request('Insights.getInsights', [
            'reportUniqueId'     => $reportUniqueId,
            'segment'            => $segment,
            'limitIncreaser'     => $limitIncreaser,
            'limitDecreaser'     => $limitDecreaser,
            'filterBy'           => $filterBy,
            'minImpactPercent'   => $minImpactPercent,
            'minGrowthPercent'   => $minGrowthPercent,
            'comparedToXPeriods' => $comparedToXPeriods,
            'orderBy'            => $orderBy,
        ], $optional);
    }

    /**
     * MODULE: LANGUAGES MANAGER
     * Manage languages
     */

    /**
     * Proof if language is available
     *
     * @param  string  $languageCode
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getLanguageAvailable(string $languageCode, array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.isLanguageAvailable', [
            'languageCode' => $languageCode,
        ], $optional);
    }

    /**
     * Get all available languages
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAvailableLanguages(array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.getAvailableLanguages', [], $optional);
    }

    /**
     * Get all available languages with information
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAvailableLanguagesInfo(array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.getAvailableLanguagesInfo', [], $optional);
    }

    /**
     * Get all available languages with their names
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAvailableLanguageNames(array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.getAvailableLanguageNames', [], $optional);
    }

    /**
     * Get translations for a language
     *
     * @param  string  $languageCode
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTranslations(string $languageCode, array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.getTranslationsForLanguage', [
            'languageCode' => $languageCode,
        ], $optional);
    }

    /**
     * Get the language for the user with the login $login
     *
     * @param  string  $login
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getLanguageForUser(string $login, array $optional = []): mixed
    {
        return $this->_request('LanguagesManager.getLanguageForUser', [
            'login' => $login,
        ], $optional);
    }

    /**
     * Set the language for the user with the login $login
     *
     * @param  string  $login
     * @param  string  $languageCode
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setLanguageForUser(
        string $login,
        string $languageCode,
        array $optional = []
    ): mixed {
        return $this->_request('LanguagesManager.setLanguageForUser', [
            'login'        => $login,
            'languageCode' => $languageCode,
        ], $optional);
    }


    /**
     * MODULE: LIVE
     * Request live data
     */

    /**
     * Get short information about the visit counts in the last minutes
     *
     * @param  int  $lastMinutes  Default: 60
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCounters(
        int $lastMinutes = 60,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Live.getCounters', [
            'lastMinutes' => $lastMinutes,
            'segment'     => $segment,
        ], $optional);
    }

    /**
     * Get information about the last visits
     *
     * @param  string  $segment
     * @param  string  $minTimestamp
     * @param  string  $doNotFetchActions
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     * @internal param int $maxIdVisit
     * @internal param int $filterLimit
     */
    public function getLastVisitsDetails(
        string $segment = '',
        string $minTimestamp = '',
        string $doNotFetchActions = '',
        array $optional = []
    ): mixed {
        return $this->_request('Live.getLastVisitsDetails', [
            'segment'           => $segment,
            'minTimestamp'      => $minTimestamp,
            'doNotFetchActions' => $doNotFetchActions,
        ], $optional);
    }

    /**
     * Get a profile for a visitor
     *
     * @param  string  $visitorId
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitorProfile(
        string $visitorId = '',
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Live.getVisitorProfile', [
            'visitorId' => $visitorId,
            'segment'   => $segment,
        ], $optional);
    }

    /**
     * Get the ID of the most recent visitor
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMostRecentVisitorId(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Live.getMostRecentVisitorId', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get userId for visitors
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersById(string $segment = '', array $optional = []): mixed
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
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function areSMSAPICredentialProvided(array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.areSMSAPICredentialProvided', [], $optional);
    }

    /**
     * Get list with sms provider
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSMSProvider(array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.getSMSProvider', [], $optional);
    }

    /**
     * Set SMSAPI credentials
     *
     * @param  string  $provider
     * @param  string  $apiKey
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setSMSAPICredential(
        string $provider,
        string $apiKey,
        array $optional = []
    ): mixed {
        return $this->_request('MobileMessaging.setSMSAPICredential', [
            'provider' => $provider,
            'apiKey'   => $apiKey,
        ], $optional);
    }

    /**
     * Add phone number
     *
     * @param  string  $phoneNumber
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addPhoneNumber(string $phoneNumber, array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.addPhoneNumber', [
            'phoneNumber' => $phoneNumber,
        ], $optional);
    }

    /**
     * Get credits left
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCreditLeft(array $optional = []): bool|object
    {
        return $this->_request('MobileMessaging.getCreditLeft', [], $optional);
    }

    /**
     * Remove phone number
     *
     * @param  string  $phoneNumber
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function removePhoneNumber(string $phoneNumber, array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.removePhoneNumber', [
            'phoneNumber' => $phoneNumber,
        ], $optional);
    }

    /**
     * Validate phone number
     *
     * @param  string  $phoneNumber
     * @param  string  $verificationCode
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function validatePhoneNumber(
        string $phoneNumber,
        string $verificationCode,
        array $optional = []
    ): mixed {
        return $this->_request('MobileMessaging.validatePhoneNumber', [
            'phoneNumber'      => $phoneNumber,
            'verificationCode' => $verificationCode,
        ], $optional);
    }

    /**
     * Delete SMSAPI credentials
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteSMSAPICredential(array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.deleteSMSAPICredential', [], $optional);
    }

    /**
     * Unknown
     *
     * @param $delegatedManagement
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setDelegatedManagement($delegatedManagement, array $optional = []): mixed
    {
        return $this->_request('MobileMessaging.setDelegatedManagement', [
            'delegatedManagement' => $delegatedManagement,
        ], $optional);
    }

    /**
     * Unknown
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDelegatedManagement(array $optional = []): mixed
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
     * @param  string  $segment
     * @param  string  $enhanced
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMultiSites(
        string $segment = '',
        string $enhanced = '',
        array $optional = []
    ): mixed {
        return $this->_request('MultiSites.getAll', [
            'segment'  => $segment,
            'enhanced' => $enhanced,
        ], $optional);
    }

    /**
     * Get key metrics about one of the sites the user manages
     *
     * @param  string  $segment
     * @param  string  $enhanced
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOne(
        string $segment = '',
        string $enhanced = '',
        array $optional = []
    ): mixed {
        return $this->_request('MultiSites.getOne', [
            'segment'  => $segment,
            'enhanced' => $enhanced,
        ], $optional);
    }

    /**
     * MODULE: OVERLAY
     */

    /**
     * Unknown
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOverlayTranslations(array $optional = []): mixed
    {
        return $this->_request('Overlay.getTranslations', [], $optional);
    }

    /**
     * Get overlay excluded query parameters
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOverlayExcludedQueryParameters(array $optional = []): mixed
    {
        return $this->_request('Overlay.getExcludedQueryParameters', [], $optional);
    }

    /**
     * Get overlay following pages
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getOverlayFollowingPages(
        string $segment = '',
        array $optional = []
    ): mixed {
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getProvider(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  string  $typeReferrer
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getReferrerType(
        string $segment = '',
        string $typeReferrer = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getReferrerType', [
            'segment'      => $segment,
            'typeReferrer' => $typeReferrer,
        ], $optional);
    }

    /**
     * Get all referrers
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAllReferrers(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getAll', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get referrer keywords
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeywords(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keywords for an url
     *
     * @param  string  $url
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeywordsForPageUrl(string $url, array $optional = []): mixed
    {
        return $this->_request('Referrers.getKeywordsForPageUrl', [
            'url' => $url,
        ], $optional);
    }

    /**
     * Get keywords for a page title
     *
     * @param  string  $title
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeywordsForPageTitle(string $title, array $optional = []): mixed
    {
        return $this->_request('Referrers.getKeywordsForPageTitle', [
            'title' => $title,
        ], $optional);
    }

    /**
     * Get search engines by keyword
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSearchEnginesFromKeywordId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getSearchEnginesFromKeywordId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get search engines
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSearchEngines(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getSearchEngines', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get search engines by search engine ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeywordsFromSearchEngineId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getKeywordsFromSearchEngineId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get campaigns
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCampaigns(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getCampaigns', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keywords by campaign ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeywordsFromCampaignId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getKeywordsFromCampaignId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get name
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingName(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getName', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get keyword content from name id
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingKeywordContentFromNameId(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getKeywordContentFromNameId', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get keyword
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingKeyword(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getKeyword', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get source     *
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingSource(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getSource', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get medium
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingMedium(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getMedium', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get content
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingContent(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getContent', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get source and medium
     * from advanced campaign reporting
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingSourceMedium(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getSourceMedium', [
            'segment' => $segment
        ], $optional);
    }

    /**
     * Get name from source and medium by ID
     * from advanced campaign reporting
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAdvancedCampaignReportingNameFromSourceMediumId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('AdvancedCampaignReporting.getNameFromSourceMediumId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment
        ], $optional);
    }

    /**
     * Get website referrerals
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getWebsites(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getWebsites', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get urls by website ID
     *
     * @param  int  $idSubtable
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUrlsFromWebsiteId(
        int $idSubtable,
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getUrlsFromWebsiteId', [
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ], $optional);
    }

    /**
     * Get social referrerals
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSocials(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getSocials', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get social referral urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUrlsForSocial(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getUrlsForSocial', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct search engines
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfSearchEngines(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('Referrers.getNumberOfDistinctSearchEngines', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct keywords
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfKeywords(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getNumberOfDistinctKeywords', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct campaigns
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfCampaigns(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getNumberOfDistinctCampaigns', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct websites
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfWebsites(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Referrers.getNumberOfDistinctWebsites', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of distinct websites urls
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfWebsitesUrls(string $segment = '', array $optional = []): mixed
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
     * @param  string  $url
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSeoRank(string $url, array $optional = []): mixed
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
     * @param  string  $description
     * @param  string  $period
     * @param  string  $hour
     * @param  string  $reportType
     * @param  string  $reportFormat
     * @param  array  $reports
     * @param  string  $parameters
     * @param  string  $idSegment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addReport(
        string $description,
        string $period,
        string $hour,
        string $reportType,
        string $reportFormat,
        array $reports,
        string $parameters,
        string $idSegment = '',
        array $optional = []
    ): mixed {
        return $this->_request('ScheduledReports.addReport', [
            'description'  => $description,
            'period'       => $period,
            'hour'         => $hour,
            'reportType'   => $reportType,
            'reportFormat' => $reportFormat,
            'reports'      => $reports,
            'parameters'   => $parameters,
            'idSegment'    => $idSegment,
        ], $optional);
    }

    /**
     * Updated scheduled report
     *
     * @param  int  $idReport
     * @param  string  $description
     * @param  string  $period
     * @param  string  $hour
     * @param  string  $reportType
     * @param  string  $reportFormat
     * @param  array  $reports
     * @param  string  $parameters
     * @param  string  $idSegment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function updateReport(
        int $idReport,
        string $description,
        string $period,
        string $hour,
        string $reportType,
        string $reportFormat,
        array $reports,
        string $parameters,
        string $idSegment = '',
        array $optional = []
    ): mixed {
        return $this->_request('ScheduledReports.updateReport', [
            'idReport'     => $idReport,
            'description'  => $description,
            'period'       => $period,
            'hour'         => $hour,
            'reportType'   => $reportType,
            'reportFormat' => $reportFormat,
            'reports'      => $reports,
            'parameters'   => $parameters,
            'idSegment'    => $idSegment,
        ], $optional);
    }

    /**
     * Delete scheduled report
     *
     * @param  int  $idReport
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteReport(int $idReport, array $optional = []): mixed
    {
        return $this->_request('ScheduledReports.deleteReport', [
            'idReport' => $idReport,
        ], $optional);
    }

    /**
     * Get list of scheduled reports
     *
     * @param  int  $idReport
     * @param  string  $ifSuperUserReturnOnlySuperUserReports
     * @param  string  $idSegment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getReports(
        int $idReport,
        string $ifSuperUserReturnOnlySuperUserReports = '',
        string $idSegment = '',
        array $optional = []
    ): mixed {
        return $this->_request('ScheduledReports.getReports', [
            'idReport'                              => $idReport,
            'ifSuperUserReturnOnlySuperUserReports' => $ifSuperUserReturnOnlySuperUserReports,
            'idSegment'                             => $idSegment,
        ], $optional);
    }

    /**
     * Get list of scheduled reports
     *
     * @param  int  $idReport
     * @param  string  $language
     * @param  string  $outputType
     * @param  string  $reportFormat
     * @param  string  $parameters
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function generateReport(
        int $idReport,
        string $language = '',
        string $outputType = '',
        string $reportFormat = '',
        string $parameters = '',
        array $optional = []
    ): mixed {
        return $this->_request('ScheduledReports.generateReport', [
            'idReport'     => $idReport,
            'language'     => $language,
            'outputType'   => $outputType,
            'reportFormat' => $reportFormat,
            'parameters'   => $parameters,
        ], $optional);
    }

    /**
     * Send scheduled reports
     *
     * @param  int  $idReport
     * @param  string  $force
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function sendReport(int $idReport, string $force = '', array $optional = []): mixed
    {
        return $this->_request('ScheduledReports.sendReport', [
            'idReport' => $idReport,
            'force'    => $force,
        ], $optional);
    }

    /**
     * MODULE: SEGMENT EDITOR
     */

    /**
     * Check if current user can add new segments
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function isUserCanAddNewSegment(array $optional = []): mixed
    {
        return $this->_request('SegmentEditor.isUserCanAddNewSegment', [], $optional);
    }

    /**
     * Delete a segment
     *
     * @param  int  $idSegment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteSegment(int $idSegment, array $optional = []): mixed
    {
        return $this->_request('SegmentEditor.delete', [
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Updates a segment
     *
     * @param  int  $idSegment
     * @param  string  $name
     * @param  string  $definition
     * @param  string  $autoArchive
     * @param  string  $enableAllUsers
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function updateSegment(
        int $idSegment,
        string $name,
        string $definition,
        string $autoArchive = '',
        string $enableAllUsers = '',
        array $optional = []
    ): mixed {
        return $this->_request('SegmentEditor.update', [
            'idSegment'      => $idSegment,
            'name'           => $name,
            'definition'     => $definition,
            'autoArchive'    => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ], $optional);
    }

    /**
     * Updates a segment
     *
     * @param  string  $name
     * @param  string  $definition
     * @param  string  $autoArchive
     * @param  string  $enableAllUsers
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addSegment(
        string $name,
        string $definition,
        string $autoArchive = '',
        string $enableAllUsers = '',
        array $optional = []
    ): mixed {
        return $this->_request('SegmentEditor.add', [
            'name'           => $name,
            'definition'     => $definition,
            'autoArchive'    => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ], $optional);
    }

    /**
     * Get a segment
     *
     * @param  int  $idSegment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSegment(int $idSegment, array $optional = []): mixed
    {
        return $this->_request('SegmentEditor.get', [
            'idSegment' => $idSegment,
        ], $optional);
    }

    /**
     * Get all segments
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAllSegments(array $optional = []): mixed
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
     * @param  string  $matomoUrl
     * @param  string  $mergeSubdomains
     * @param  string  $groupPageTitlesByDomain
     * @param  string  $mergeAliasUrls
     * @param  string  $visitorCustomVariables
     * @param  string  $pageCustomVariables
     * @param  string  $customCampaignNameQueryParam
     * @param  string  $customCampaignKeywordParam
     * @param  string  $doNotTrack
     * @param  string  $disableCookies
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getJavascriptTag(
        string $matomoUrl,
        string $mergeSubdomains = '',
        string $groupPageTitlesByDomain = '',
        string $mergeAliasUrls = '',
        string $visitorCustomVariables = '',
        string $pageCustomVariables = '',
        string $customCampaignNameQueryParam = '',
        string $customCampaignKeywordParam = '',
        string $doNotTrack = '',
        string $disableCookies = '',
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.getJavascriptTag', [
            'piwikUrl'                     => $matomoUrl,
            'mergeSubdomains'              => $mergeSubdomains,
            'groupPageTitlesByDomain'      => $groupPageTitlesByDomain,
            'mergeAliasUrls'               => $mergeAliasUrls,
            'visitorCustomVariables'       => $visitorCustomVariables,
            'pageCustomVariables'          => $pageCustomVariables,
            'customCampaignNameQueryParam' => $customCampaignNameQueryParam,
            'customCampaignKeywordParam'   => $customCampaignKeywordParam,
            'doNotTrack'                   => $doNotTrack,
            'disableCookies'               => $disableCookies,
        ], $optional);
    }

    /**
     * Get image tracking code of the current site
     *
     * @param  string  $matomoUrl
     * @param  string  $actionName
     * @param  string  $idGoal
     * @param  string  $revenue
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getImageTrackingCode(
        string $matomoUrl,
        string $actionName = '',
        string $idGoal = '',
        string $revenue = '',
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.getImageTrackingCode', [
            'piwikUrl'   => $matomoUrl,
            'actionName' => $actionName,
            'idGoal'     => $idGoal,
            'revenue'    => $revenue,
        ], $optional);
    }

    /**
     * Get sites from a group
     *
     * @param  string  $group
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesFromGroup(string $group, array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesFromGroup', [
            'group' => $group,
        ], $optional);
    }

    /**
     * Get all site groups.
     * Requires superuser access.
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesGroups(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesGroups', [], $optional);
    }

    /**
     * Get information about the current site
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSiteInformation(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSiteFromId', [], $optional);
    }

    /**
     * Get urls from current site
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSiteUrls(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSiteUrlsFromId', [], $optional);
    }

    /**
     * Get all sites
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAllSites(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getAllSites', [], $optional);
    }

    /**
     * Get all sites with ID
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getAllSitesId(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getAllSitesId', [], $optional);
    }

    /**
     * Get all sites where the current user has admin access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesWithAdminAccess(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has view access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesWithViewAccess(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesWithViewAccess', [], $optional);
    }

    /**
     * Get all sites where the current user has at least view access
     *
     * @param  string  $limit
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesWithAtLeastViewAccess(
        string $limit = '',
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.getSitesWithAtLeastViewAccess', [
            'limit' => $limit,
        ], $optional);
    }

    /**
     * Get all sites with ID where the current user has admin access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesIdWithAdminAccess(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesIdWithAdminAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has view access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesIdWithViewAccess(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesIdWithViewAccess', [], $optional);
    }

    /**
     * Get all sites with ID where the current user has at least view access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesIdWithAtLeastViewAccess(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesIdWithAtLeastViewAccess', [], $optional);
    }

    /**
     * Get a site by its URL
     *
     * @param  string  $url
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesIdFromSiteUrl(string $url, array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSitesIdFromSiteUrl', [
            'url' => $url,
        ], $optional);
    }

    /**
     * Get a list of all available settings for a specific site.
     *
     * @return object|bool
     * @throws InvalidRequestException
     * @throws JsonException|InvalidResponseException
     */
    public function getSiteSettings(): mixed
    {
        return $this->_request('SitesManager.getSiteSettings');
    }

    /**
     * Add a website.
     * Requires Super User access.
     *
     * The website is defined by a name and an array of URLs.
     *
     * @param  string  $siteName  Site name
     * @param  string  $urls  Comma separated list of urls
     * @param  string  $ecommerce  Is Ecommerce Reporting enabled for this website?
     * @param  string  $siteSearch
     * @param  string  $searchKeywordParameters  Comma separated list of search keyword parameter names
     * @param  string  $searchCategoryParameters  Comma separated list of search category parameter names
     * @param  string  $excludeIps  Comma separated list of IPs to exclude from the reports (allows wildcards)
     * @param  string  $excludedQueryParameters
     * @param  string  $timezone  Timezone string, eg. 'Europe/London'
     * @param  string  $currency  Currency, eg. 'EUR'
     * @param  string  $group  Website group identifier
     * @param  string  $startDate  Date at which the statistics for this website will start. Defaults to today's date in
     *     YYYY-MM-DD format
     * @param  string  $excludedUserAgents
     * @param  string  $keepURLFragments  If 1, URL fragments will be kept when tracking. If 2, they
     *                                 will be removed. If 0, the default global behavior will be used.
     * @param  string  $settingValues  JSON serialized settings eg {settingName: settingValue, ...}
     * @param  string  $type  The website type, defaults to "website" if not set.
     * @param  string  $excludeUnknownUrls  Track only URL matching one of website URLs
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addSite(
        string $siteName,
        string $urls,
        string $ecommerce = '',
        string $siteSearch = '',
        string $searchKeywordParameters = '',
        string $searchCategoryParameters = '',
        string $excludeIps = '',
        string $excludedQueryParameters = '',
        string $timezone = '',
        string $currency = '',
        string $group = '',
        string $startDate = '',
        string $excludedUserAgents = '',
        string $keepURLFragments = '',
        string $settingValues = '',
        string $type = '',
        string $excludeUnknownUrls = '',
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.addSite', [
            'siteName'                 => $siteName,
            'urls'                     => $urls,
            'ecommerce'                => $ecommerce,
            'siteSearch'               => $siteSearch,
            'searchKeywordParameters'  => $searchKeywordParameters,
            'searchCategoryParameters' => $searchCategoryParameters,
            'excludeIps'               => $excludeIps,
            'excludedQueryParameters'  => $excludedQueryParameters,
            'timezone'                 => $timezone,
            'currency'                 => $currency,
            'group'                    => $group,
            'startDate'                => $startDate,
            'excludedUserAgents'       => $excludedUserAgents,
            'keepURLFragments'         => $keepURLFragments,
            'settingValues'            => $settingValues,
            'type'                     => $type,
            'excludeUnknownUrls'       => $excludeUnknownUrls,
        ], $optional);
    }

    /**
     * Delete current site
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteSite(array $optional = []): mixed
    {
        return $this->_request('SitesManager.deleteSite', [], $optional);
    }

    /**
     * Add alias urls for the current site
     *
     * @param  array  $urls
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addSiteAliasUrls(array $urls, array $optional = []): mixed
    {
        return $this->_request('SitesManager.addSiteAliasUrls', [
            'urls' => $urls,
        ], $optional);
    }

    /**
     * Set alias urls for the current site
     *
     * @param  array  $urls
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setSiteAliasUrls(array $urls, array $optional = []): mixed
    {
        return $this->_request('SitesManager.setSiteAliasUrls', [
            'urls' => $urls,
        ], $optional);
    }

    /**
     * Get IP's for a specific range
     *
     * @param  string  $ipRange
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getIpsForRange(string $ipRange, array $optional = []): mixed
    {
        return $this->_request('SitesManager.getIpsForRange', [
            'ipRange' => $ipRange,
        ], $optional);
    }

    /**
     * Set the global excluded IP's
     *
     * @param  array  $excludedIps
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setExcludedIps(array $excludedIps, array $optional = []): mixed
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
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setGlobalSearchParameters(
        $searchKeywordParameters,
        $searchCategoryParameters,
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.setGlobalSearchParameters ', [
            'searchKeywordParameters'  => $searchKeywordParameters,
            'searchCategoryParameters' => $searchCategoryParameters,
        ], $optional);
    }

    /**
     * Get search keywords
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSearchKeywordParametersGlobal(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSearchKeywordParametersGlobal', [], $optional);
    }

    /**
     * Get search categories
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSearchCategoryParametersGlobal(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getSearchCategoryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded query parameters
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExcludedParameters(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getExcludedQueryParametersGlobal', [], $optional);
    }

    /**
     * Get the global excluded user agents
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExcludedUserAgentsGlobal(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getExcludedUserAgentsGlobal', [], $optional);
    }

    /**
     * Set the global excluded user agents
     *
     * @param  array  $excludedUserAgents
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setGlobalExcludedUserAgents(
        array $excludedUserAgents,
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.setGlobalExcludedUserAgents', [
            'excludedUserAgents' => $excludedUserAgents,
        ], $optional);
    }

    /**
     * Check if site specific user agent exclude is enabled
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function isSiteSpecificUserAgentExcludeEnabled(array $optional = []): mixed
    {
        return $this->_request('SitesManager.isSiteSpecificUserAgentExcludeEnabled', [], $optional);
    }

    /**
     * Set site specific user agent exclude
     *
     * @param  int  $enabled
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setSiteSpecificUserAgentExcludeEnabled(
        int $enabled,
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.setSiteSpecificUserAgentExcludeEnabled', [
            'enabled' => $enabled,
        ], $optional);
    }

    /**
     * Check if the url fragments should be global
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getKeepURLFragmentsGlobal(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getKeepURLFragmentsGlobal', [], $optional);
    }

    /**
     * Set the url fragments global
     *
     * @param  int  $enabled
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setKeepURLFragmentsGlobal(int $enabled, array $optional = []): mixed
    {
        return $this->_request('SitesManager.setKeepURLFragmentsGlobal', [
            'enabled' => $enabled,
        ], $optional);
    }

    /**
     * Set the global excluded query parameters
     *
     * @param  array  $excludedQueryParameters
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setExcludedParameters(
        array $excludedQueryParameters,
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.setGlobalExcludedQueryParameters', [
            'excludedQueryParameters' => $excludedQueryParameters,
        ], $optional);
    }

    /**
     * Get the global excluded IP's
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getExcludedIps(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getExcludedIpsGlobal', [], $optional);
    }

    /**
     * Get the default currency
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultCurrency(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getDefaultCurrency', [], $optional);
    }

    /**
     * Set the default currency
     *
     * @param  string  $defaultCurrency
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setDefaultCurrency(string $defaultCurrency, array $optional = []): mixed
    {
        return $this->_request('SitesManager.setDefaultCurrency', [
            'defaultCurrency' => $defaultCurrency,
        ], $optional);
    }

    /**
     * Get the default timezone
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getDefaultTimezone(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getDefaultTimezone', [], $optional);
    }

    /**
     * Set the default timezone
     *
     * @param  string  $defaultTimezone
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setDefaultTimezone(string $defaultTimezone, array $optional = []): mixed
    {
        return $this->_request('SitesManager.setDefaultTimezone', [
            'defaultTimezone' => $defaultTimezone,
        ], $optional);
    }

    /**
     * Update current site
     *
     * @param  string  $siteName
     * @param  array  $urls
     * @param  bool|string  $ecommerce
     * @param  bool|string  $siteSearch
     * @param  string  $searchKeywordParameters
     * @param  string  $searchCategoryParameters
     * @param  array|string  $excludeIps
     * @param  array|string  $excludedQueryParameters
     * @param  string  $timezone
     * @param  string  $currency
     * @param  string  $group
     * @param  string  $startDate
     * @param  string  $excludedUserAgents
     * @param  string  $keepURLFragments
     * @param  string  $type
     * @param  string  $settings
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function updateSite(
        string $siteName,
        array $urls,
        bool|string $ecommerce = '',
        bool|string $siteSearch = '',
        string $searchKeywordParameters = '',
        string $searchCategoryParameters = '',
        array|string $excludeIps = '',
        array|string $excludedQueryParameters = '',
        string $timezone = '',
        string $currency = '',
        string $group = '',
        string $startDate = '',
        string $excludedUserAgents = '',
        string $keepURLFragments = '',
        string $type = '',
        string $settings = '',
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.updateSite', [
            'siteName'                 => $siteName,
            'urls'                     => $urls,
            'ecommerce'                => $ecommerce,
            'siteSearch'               => $siteSearch,
            'searchKeywordParameters'  => $searchKeywordParameters,
            'searchCategoryParameters' => $searchCategoryParameters,
            'excludeIps'               => $excludeIps,
            'excludedQueryParameters'  => $excludedQueryParameters,
            'timezone'                 => $timezone,
            'currency'                 => $currency,
            'group'                    => $group,
            'startDate'                => $startDate,
            'excludedUserAgents'       => $excludedUserAgents,
            'keepURLFragments'         => $keepURLFragments,
            'type'                     => $type,
            'settings'                 => $settings,
        ], $optional);
    }

    /**
     * Get a list with all available currencies
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCurrencyList(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getCurrencyList', [], $optional);
    }

    /**
     * Get a list with all currency symbols
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCurrencySymbols(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getCurrencySymbols', [], $optional);
    }

    /**
     * Get a list with available timezones
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTimezonesList(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getTimezonesList', [], $optional);
    }

    /**
     * Unknown
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUniqueSiteTimezones(array $optional = []): mixed
    {
        return $this->_request('SitesManager.getUniqueSiteTimezones', [], $optional);
    }

    /**
     * Rename group
     *
     * @param  string  $oldGroupName
     * @param  string  $newGroupName
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function renameGroup(
        string $oldGroupName,
        string $newGroupName,
        array $optional = []
    ): mixed {
        return $this->_request('SitesManager.renameGroup', [
            'oldGroupName' => $oldGroupName,
            'newGroupName' => $newGroupName,
        ], $optional);
    }

    /**
     * Get all sites which matches the pattern
     *
     * @param  string  $pattern
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getPatternMatchSites(string $pattern, array $optional = []): mixed
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
     * @param  string  $segment
     * @param  string  $limitBeforeGrouping
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTransitionsForPageTitle(
        $pageTitle,
        string $segment = '',
        string $limitBeforeGrouping = '',
        array $optional = []
    ): mixed {
        return $this->_request('Transitions.getTransitionsForPageTitle', [
            'pageTitle'           => $pageTitle,
            'segment'             => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ], $optional);
    }

    /**
     * Get transitions for a page URL
     *
     * @param $pageUrl
     * @param  string  $segment
     * @param  string  $limitBeforeGrouping
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTransitionsForPageUrl(
        $pageUrl,
        string $segment = '',
        string $limitBeforeGrouping = '',
        array $optional = []
    ): mixed {
        return $this->_request('Transitions.getTransitionsForPageTitle', [
            'pageUrl'             => $pageUrl,
            'segment'             => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ], $optional);
    }

    /**
     * Get transitions for a page URL
     *
     * @param $actionName
     * @param $actionType
     * @param  string  $segment
     * @param  string  $limitBeforeGrouping
     * @param  string  $parts
     * @param  string  $returnNormalizedUrls
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTransitionsForAction(
        $actionName,
        $actionType,
        string $segment = '',
        string $limitBeforeGrouping = '',
        string $parts = 'all',
        string $returnNormalizedUrls = '',
        array $optional = []
    ): mixed {
        return $this->_request('Transitions.getTransitionsForAction', [
            'actionName'           => $actionName,
            'actionType'           => $actionType,
            'segment'              => $segment,
            'limitBeforeGrouping'  => $limitBeforeGrouping,
            'parts'                => $parts,
            'returnNormalizedUrls' => $returnNormalizedUrls,
        ], $optional);
    }

    /**
     * Get translations for the transitions
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTransitionsTranslations(array $optional = []): mixed
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCountry(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('UserCountry.getCountry', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get a list of used country codes to country names
     *
     * @return object|bool
     * @throws InvalidRequestException
     * @throws JsonException|InvalidResponseException
     */
    public function getCountryCodeMapping(): mixed
    {
        return $this->_request('UserCountry.getCountryCodeMapping');
    }

    /**
     * Get continents of all visitors
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getContinent(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('UserCountry.getContinent', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get regions of all visitors
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getRegion(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('UserCountry.getRegion', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get cities of all visitors
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCity(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('UserCountry.getCity', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get location from ip
     *
     * @param  string  $ip
     * @param  string  $provider
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getLocationFromIP(
        string $ip,
        string $provider = '',
        array $optional = []
    ): mixed {
        return $this->_request('UserCountry.getLocationFromIP', [
            'ip'       => $ip,
            'provider' => $provider,
        ], $optional);
    }

    /**
     * Get the number of disting countries
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getCountryNumber(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getResolution(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('Resolution.getResolution', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get configuration
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getConfiguration(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserPlugin(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserLanguage(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('UserLanguage.getLanguage', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get language code
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserLanguageCode(string $segment = '', array $optional = []): mixed
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
     * @param  string  $userLogin  Username
     * @param  string  $preferenceName
     * @param  string  $preferenceValue
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setUserPreference(
        string $userLogin,
        string $preferenceName,
        string $preferenceValue,
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.setUserPreference', [
            'userLogin'       => $userLogin,
            'preferenceName'  => $preferenceName,
            'preferenceValue' => $preferenceValue,
        ], $optional);
    }

    /**
     * Get user preference
     *
     * @param  string  $userLogin  Username
     * @param  string  $preferenceName
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserPreference(
        string $userLogin,
        string $preferenceName,
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.getUserPreference', [
            'userLogin'      => $userLogin,
            'preferenceName' => $preferenceName,
        ], $optional);
    }

    /**
     * Get user by username
     *
     * @param  string  $userLogins  Comma separated list with usernames
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsers(string $userLogins = '', array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsers', [
            'userLogins' => $userLogins,
        ], $optional);
    }

    /**
     * Get all user logins
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersLogin(array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsersLogin', [], $optional);
    }

    /**
     * Get sites by user access
     *
     * @param  string  $access
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersSitesFromAccess(string $access, array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsersSitesFromAccess', [
            'access' => $access,
        ], $optional);
    }

    /**
     * Get all users with access level from the current site
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersAccess(array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsersAccessFromSite', [], $optional);
    }

    /**
     * Get all users with access $access to the current site
     *
     * @param  string  $access
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersWithSiteAccess(string $access, array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsersWithSiteAccess', [
            'access' => $access,
        ], $optional);
    }

    /**
     * Get site access from the user $userLogin
     *
     * @param  string  $userLogin  Username
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSitesAccessFromUser(string $userLogin, array $optional = []): mixed
    {
        return $this->_request('UsersManager.getSitesAccessFromUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Get user by login
     *
     * @param  string  $userLogin  Username
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUser(string $userLogin, array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Get user by email
     *
     * @param  string  $userEmail
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserByEmail(string $userEmail, array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUserByEmail', [
            'userEmail' => $userEmail,
        ], $optional);
    }

    /**
     * Add a user
     *
     * @param  string  $userLogin  Username
     * @param  string  $password  Password in clear text
     * @param  string  $email
     * @param  string  $alias
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function addUser(
        string $userLogin,
        string $password,
        string $email,
        string $alias = '',
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.addUser', [
            'userLogin' => $userLogin,
            'password'  => $password,
            'email'     => $email,
            'alias'     => $alias,
        ], $optional);
    }

    /**
     * Set superuser access
     *
     * @param  string  $userLogin  Username
     * @param  int  $hasSuperUserAccess
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setSuperUserAccess(
        string $userLogin,
        int $hasSuperUserAccess,
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.setSuperUserAccess', [
            'userLogin'          => $userLogin,
            'hasSuperUserAccess' => $hasSuperUserAccess,
        ], $optional);
    }

    /**
     * Check if user has superuser access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function hasSuperUserAccess(array $optional = []): mixed
    {
        return $this->_request('UsersManager.hasSuperUserAccess', [], $optional);
    }

    /**
     * Get a list of users with superuser access
     *
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUsersHavingSuperUserAccess(array $optional = []): mixed
    {
        return $this->_request('UsersManager.getUsersHavingSuperUserAccess', [], $optional);
    }

    /**
     * Update a user
     *
     * @param  string  $userLogin  Username
     * @param  string  $password  Password in clear text
     * @param  string  $email
     * @param  string  $alias
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function updateUser(
        string $userLogin,
        string $password = '',
        string $email = '',
        string $alias = '',
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.updateUser', [
            'userLogin' => $userLogin,
            'password'  => $password,
            'email'     => $email,
            'alias'     => $alias,
        ], $optional);
    }

    /**
     * Delete a user
     *
     * @param  string  $userLogin  Username
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function deleteUser(string $userLogin, array $optional = []): mixed
    {
        return $this->_request('UsersManager.deleteUser', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Checks if a user exist
     *
     * @param  string  $userLogin
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function userExists(string $userLogin, array $optional = []): mixed
    {
        return $this->_request('UsersManager.userExists', [
            'userLogin' => $userLogin,
        ], $optional);
    }

    /**
     * Checks if a user exist by email
     *
     * @param  string  $userEmail
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function userEmailExists(string $userEmail, array $optional = []): mixed
    {
        return $this->_request('UsersManager.userEmailExists', [
            'userEmail' => $userEmail,
        ], $optional);
    }

    /**
     * Grant access to multiple sites
     *
     * @param  string  $userLogin  Username
     * @param  string  $access
     * @param  array  $idSites
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function setUserAccess(
        string $userLogin,
        string $access,
        array $idSites,
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.setUserAccess', [
            'userLogin' => $userLogin,
            'access'    => $access,
            'idSites'   => $idSites,
        ], $optional);
    }

    /**
     * Get the token for a user
     *
     * @param  string  $userLogin  Username
     * @param  string  $md5Password  Password in clear text
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getTokenAuth(
        string $userLogin,
        string $md5Password,
        array $optional = []
    ): mixed {
        return $this->_request('UsersManager.getTokenAuth', [
            'userLogin'   => $userLogin,
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
     * @param  string  $segment
     * @param  array  $columns
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitFrequency(
        string $segment = '',
        array $columns = [],
        array $optional = []
    ): mixed {
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitLocalTime(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitTime.getVisitInformationPerLocalTime', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the visit by server time
     *
     * @param  string  $segment
     * @param  string  $hideFutureHoursWhenToday  Hide the future hours when the report is created for today
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitServerTime(
        string $segment = '',
        string $hideFutureHoursWhenToday = '',
        array $optional = []
    ): mixed {
        return $this->_request('VisitTime.getVisitInformationPerServerTime', [
            'segment'                  => $segment,
            'hideFutureHoursWhenToday' => $hideFutureHoursWhenToday,
        ], $optional);
    }

    /**
     * Get the visit by server time
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getByDayOfWeek(string $segment = '', array $optional = []): mixed
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
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfVisitsPerDuration(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('VisitorInterest.getNumberOfVisitsPerVisitDuration', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of visits per visited page
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfVisitsPerPage(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('VisitorInterest.getNumberOfVisitsPerPage', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of days elapsed since the last visit
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfVisitsByDaySinceLast(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('VisitorInterest.getNumberOfVisitsByDaysSinceLast', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the number of visits by visit count
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getNumberOfVisitsByCount(
        string $segment = '',
        array $optional = []
    ): mixed {
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
     * @param  string  $segment
     * @param  array  $columns
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitsSummary(
        string $segment = '',
        array $columns = [],
        array $optional = []
    ): mixed {
        return $this->_request('VisitsSummary.get', [
            'segment' => $segment,
            'columns' => $columns,
        ], $optional);
    }

    /**
     * Get visits
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisits(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getVisits', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get unique visits
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUniqueVisitors(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getUniqueVisitors', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get user visit summary
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getUserVisitors(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getUsers', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get actions
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getActions(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getActions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get max actions
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getMaxActions(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getMaxActions', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get bounce count
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getBounceCount(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getBounceCount', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get converted visits
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getVisitsConverted(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getVisitsConverted', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the sum of all visit lengths
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSumVisitsLength(string $segment = '', array $optional = []): mixed
    {
        return $this->_request('VisitsSummary.getSumVisitsLength', [
            'segment' => $segment,
        ], $optional);
    }

    /**
     * Get the sum of all visit lengths formated in the current language
     *
     * @param  string  $segment
     * @param  array  $optional
     *
     * @return bool|object
     * @@throws InvalidRequestException|JsonException|InvalidResponseException
     */
    public function getSumVisitsLengthPretty(
        string $segment = '',
        array $optional = []
    ): mixed {
        return $this->_request('VisitsSummary.getSumVisitsLengthPretty', [
            'segment' => $segment,
        ], $optional);
    }
}
