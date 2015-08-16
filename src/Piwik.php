<?php namespace VisualAppeal;

/**
 * Repository: https://github.com/VisualAppeal/Piwik-PHP-API
 * Official api reference: http://piwik.org/docs/analytics-api/reference/
 */
class Piwik
{
  const ERROR_INVALID = 10;
  const ERROR_EMPTY   = 11;

  const PERIOD_DAY   = 'day';
  const PERIOD_WEEK  = 'week';
  const PERIOD_MONTH = 'month';
  const PERIOD_YEAR  = 'year';
  const PERIOD_RANGE = 'range';

  const DATE_TODAY     = 'today';
  const DATE_YESTERDAY = 'yesterday';

  const FORMAT_XML  = 'xml';
  const FORMAT_JSON = 'json';
  const FORMAT_CSV  = 'csv';
  const FORMAT_TSV  = 'tsv';
  const FORMAT_HTML = 'html';
  const FORMAT_RSS  = 'rss';
  const FORMAT_PHP  = 'php';

  const GRAPH_EVOLUTION    = 'evolution';
  const GRAPH_VERTICAL_BAR = 'verticalBar';
  const GRAPH_PIE          = 'pie';
  const GRAPH_PIE_3D       = '3dPie';

  /**
   * verify ssl-connection via curl
   *
   * @var bool
   */
  public $verifySsl = true;

  /**
   * @var string
   */
  private $_site = '';

  /**
   * @var string
   */
  private $_token = '';

  /**
   * @var int
   */
  private $_siteId = 0;

  /**
   * @var string
   */
  private $_format = self::FORMAT_PHP;

  /**
   * @var string
   */
  private $_language = 'en';

  /**
   * @var string
   */
  private $_period = self::PERIOD_DAY;

  /**
   * @var string
   */
  private $_date = '';

  /**
   * @var string
   */
  private $_rangeStart = 'yesterday';

  /**
   * @var null|string
   */
  private $_rangeEnd = null;

  /**
   * @var bool
   */
  private $_isJsonDecodeAssoc = false;

  /**
   * @var string
   */
  private $_limit = '';

  /**
   * @var array
   */
  private $_errors = array();

  /**
   * Create new instance
   *
   * @param string $site   URL of the piwik installation
   * @param string $token  API Access token
   * @param int    $siteId ID of the site
   * @param string $format
   * @param string $period
   * @param string $date
   * @param string $rangeStart
   * @param string $rangeEnd
   */
  function __construct($site, $token, $siteId, $format = self::FORMAT_JSON, $period = self::PERIOD_DAY, $date = self::DATE_YESTERDAY, $rangeStart = '', $rangeEnd = null)
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
   * Set date range
   *
   * @param string $rangeStart e.g. 2012-02-10 (YYYY-mm-dd) or last5(lastX), previous12(previousY)...
   * @param string $rangeEnd   e.g. 2012-02-12. Leave this parameter empty to request all data from
   *                           $rangeStart until now
   *
   * @return $this
   */
  public function setRange($rangeStart, $rangeEnd = null)
  {
    $this->_date = '';
    $this->_rangeStart = $rangeStart;
    $this->_rangeEnd = $rangeEnd;

    if (null === $rangeEnd) {

      if (
          strpos($rangeStart, 'last') !== false
          ||
          strpos($rangeStart, 'previous') !== false
      ) {
        $this->setDate($rangeStart);
      } else {
        $this->_rangeEnd = self::DATE_TODAY;
      }

    }

    return $this;
  }

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
   *
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
   *
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
    return (int)$this->_siteId;
  }

  /**
   * Set current site ID
   *
   * @param int $id
   *
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
   *    <br>FORMAT_XML
   *    <br>FORMAT_JSON
   *    <br>FORMAT_CSV
   *    <br>FORMAT_TSV
   *    <br>FORMAT_HTML
   *    <br>FORMAT_RSS
   *    <br>FORMAT_PHP
   *
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
   *
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
   *                     <br>DATE_TODAY
   *                     <br>DATE_YESTERDAY
   *
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
   *    <br>PERIOD_DAY
   *    <br>PERIOD_MONTH
   *    <br>PERIOD_WEEK
   *    <br>PERIOD_YEAR
   *    <br>PERIOD_RANGE
   *
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
   *
   * @return $this
   */
  public function setLimit($limit)
  {
    $this->_limit = $limit;

    return $this;
  }

  /**
   * Return if JSON decode an associate array
   *
   * @return boolean
   */
  public function isIsJsonDecodeAssoc()
  {
    return $this->_isJsonDecodeAssoc;
  }

  /**
   * Sets the json_decode format
   *
   * @param boolean $isJsonDecodeAssoc false decode as Object, true for decode as Associate array
   */
  public function setIsJsonDecodeAssoc($isJsonDecodeAssoc)
  {
    $this->_isJsonDecodeAssoc = $isJsonDecodeAssoc;
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

    $this->_errors = array();

    return $this;
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
   * Error methods
   */

  /**
   * Get current piwik version
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getPiwikVersion($optional = array())
  {
    return $this->_request(
        'API.getPiwikVersion',
        array(),
        $optional
    );
  }

  /**
   * Make API request
   *
   * @param string $method
   * @param array  $params
   * @param array  $optional
   *
   * @return bool
   */
  private function _request($method, $params = array(),
                            $optional = array())
  {
    $url = $this->_parseUrl($method, $params + $optional);
    if ($url === false) {
      return false;
    }

    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);

    if (!$this->verifySsl) {
      curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    }

    $buffer = $this->curl_redirect_exec($handle);
    curl_close($handle);

    if (!empty($buffer)) {
      $request = $this->_parseRequest($buffer);
    } else {
      $request = false;
    }

    return $this->_finishRequest($request, $method, $params + $optional);
  }

  /**
   * Create request url with parameters
   *
   * @param string $method The request method
   * @param array  $params Request params
   *
   * @return bool|string
   */
  private function _parseUrl($method, array $params = array())
  {
    $paramsDefault = array(
        'module'     => 'API',
        'method'     => $method,
        'token_auth' => $this->_token,
        'idSite'     => $this->_siteId,
        'period'     => $this->_period,
        'format'     => $this->_format,
        'language'   => $this->_language,
    );
    $params = $paramsDefault + $params;

    foreach ($params as $key => $value) {
      $params[$key] = urlencode($value);
    }

    if (
        !empty($this->_rangeStart)
        &&
        !empty($this->_rangeEnd)
    ) {
      $params += array('date' => $this->_rangeStart . ',' . $this->_rangeEnd,);
    } elseif (!empty($this->_date)) {
      $params += array('date' => $this->_date,);
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
   * Add error
   *
   * @param string $msg Error message
   */
  protected function _addError($msg = '')
  {
    $this->_errors[] = $msg;
  }

  /**
   * Fallback for "CURLOPT_FOLLOWLOCATION"
   * if "safe_mode" is on or "open_basedir" is set
   *
   * @param $curlHandler
   *
   * @return string|false false on error
   */
  private function curl_redirect_exec($curlHandler)
  {
    curl_setopt($curlHandler, CURLOPT_HEADER, true);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($curlHandler);

    // follow the redirect (301 || 302)
    $http_code = curl_getinfo($curlHandler, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
      list($header) = explode("\r\n\r\n", $data, 2);
      $matches = array();
      preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
      $url = trim(array_pop($matches));
      $url_parsed = parse_url($url);

      if (isset($url_parsed)) {
        curl_setopt($curlHandler, CURLOPT_URL, $url);

        return $this->curl_redirect_exec($curlHandler);
      }
    }

    list(, $body) = explode("\r\n\r\n", $data, 2);

    return $body;
  }

  /**
   * Parse request result
   *
   * @param $request
   *
   * @return mixed
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
   * Validate request and return the values
   *
   * @param        $request
   * @param string $method
   * @param array  $params
   *
   * @return bool
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
   * Validate the request result
   *
   * @param $request
   *
   * @return mixed
   */
  private function _validRequest($request)
  {
    if ($request !== false && null !== $request) {

      if (!isset($request->result) || $request->result != 'error') {
        return true;
      }

      return $request->message;
    }

    if (null === $request) {
      return self::ERROR_EMPTY;
    } else {
      return self::ERROR_INVALID;
    }
  }

  /**
   * Get current ip address (from the server executing this script)
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getIpFromHeader($optional = array())
  {
    return $this->_request(
        'API.getIpFromHeader',
        array(),
        $optional
    );
  }

  /**
   * Get current settings
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSettings($optional = array())
  {
    return $this->_request(
        'API.getSettings',
        array(),
        $optional
    );
  }

  /**
   * Get default metric translations
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultMetricTranslations($optional = array())
  {
    return $this->_request(
        'API.getDefaultMetricTranslations',
        array(),
        $optional
    );
  }

  /**
   * Get default metrics
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultMetrics($optional = array())
  {
    return $this->_request(
        'API.getDefaultMetrics',
        array(),
        $optional
    );
  }

  /**
   * Get default processed metrics
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultProcessedMetrics($optional = array())
  {
    return $this->_request(
        'API.getDefaultProcessedMetrics',
        array(),
        $optional
    );
  }

  /**
   * Get default metrics documentation
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultMetricsDocumentation($optional = array())
  {
    return $this->_request(
        'API.getDefaultMetricsDocumentation',
        array(),
        $optional
    );
  }

  /**
   * Get default metric translations
   *
   * @param array $sites Array with the ID's of the sites
   * @param array $optional
   *
   * @return bool
   */
  public function getSegmentsMetadata($sites = array(),
                                      $optional = array())
  {
    return $this->_request(
        'API.getSegmentsMetadata',
        array(
            'idSites' => $sites,
        ),
        $optional
    );
  }

  /**
   * Get the url of the logo
   *
   * @param boolean $pathOnly Return the url (false) or the absolute path (true)
   * @param array   $optional
   *
   * @return bool
   */
  public function getLogoUrl($pathOnly = false, $optional = array())
  {
    return $this->_request(
        'API.getLogoUrl',
        array(
            'pathOnly' => $pathOnly,
        ),
        $optional
    );
  }

  /**
   * Get the url of the header logo
   *
   * @param boolean $pathOnly Return the url (false) or the absolute path (true)
   * @param array   $optional
   *
   * @return bool
   */
  public function getHeaderLogoUrl($pathOnly = false, $optional = array())
  {
    return $this->_request(
        'API.getHeaderLogoUrl',
        array(
            'pathOnly' => $pathOnly,
        ),
        $optional
    );
  }

  /**
   * Get metadata from the API
   *
   * @param string $apiModule     Module
   * @param string $apiAction     Action
   * @param array  $apiParameters Parameters
   * @param array  $optional
   *
   * @return bool
   */
  public function getMetadata($apiModule, $apiAction, $apiParameters = array(),
                              $optional = array())
  {
    return $this->_request(
        'API.getMetadata',
        array(
            'apiModule'     => $apiModule,
            'apiAction'     => $apiAction,
            'apiParameters' => $apiParameters,
        ),
        $optional
    );
  }

  /**
   * Get metadata from a report
   *
   * @param array  $idSites Array with the ID's of the sites
   * @param string $hideMetricsDoc
   * @param string $showSubtableReports
   * @param array  $optional
   *
   * @return bool
   */
  public function getReportMetadata(array $idSites, $hideMetricsDoc = '', $showSubtableReports = '',
                                    $optional = array())
  {
    return $this->_request(
        'API.getReportMetadata',
        array(
            'idSites'             => $idSites,
            'hideMetricsDoc'      => $hideMetricsDoc,
            'showSubtableReports' => $showSubtableReports,
        ),
        $optional
    );
  }

  /**
   * Get processed report
   *
   * @param string         $apiModule Module
   * @param string         $apiAction Action
   * @param string         $segment
   * @param array          $apiParameters
   * @param int|string     $idGoal
   * @param boolean|string $showTimer
   * @param string         $hideMetricsDoc
   * @param array          $optional
   *
   * @return bool
   */
  public function getProcessedReport($apiModule, $apiAction, $segment = '', $apiParameters = array(),
                                     $idGoal = '', $showTimer = '1', $hideMetricsDoc = '', $optional = array())
  {
    return $this->_request(
        'API.getProcessedReport',
        array(
            'apiModule'      => $apiModule,
            'apiAction'      => $apiAction,
            'segment'        => $segment,
            'apiParameters'  => $apiParameters,
            'idGoal'         => $idGoal,
            'showTimer'      => $showTimer,
            'hideMetricsDoc' => $hideMetricsDoc,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param string $segment
   * @param string $columns
   * @param array  $optional
   *
   * @return bool
   */
  public function getApi($segment = '', $columns = '', $optional = array())
  {
    return $this->_request(
        'API.get',
        array(
            'segment' => $segment,
            'columns' => $columns,
        ),
        $optional
    );
  }

  /**
   * MODULE: ACTIONS
   * Reports for visitor actions
   */

  /**
   * Unknown
   *
   * @param        $apiModule
   * @param        $apiAction
   * @param string $segment
   * @param        $column
   * @param string $idGoal
   * @param string $legendAppendMetric
   * @param string $labelUseAbsoluteUrl
   * @param array  $optional
   *
   * @return bool
   */
  public function getRowEvolution($apiModule, $apiAction, $segment = '', $column = '', $idGoal = '',
                                  $legendAppendMetric = '1', $labelUseAbsoluteUrl = '1', $optional = array())
  {
    return $this->_request(
        'API.getRowEvolution',
        array(
            'apiModule'             => $apiModule,
            'apiAction'             => $apiAction,
            'segment'               => $segment,
            'column'                => $column,
            'idGoal'                => $idGoal,
            'legendAppendMetric '   => $legendAppendMetric,
            'labelUseAbsoluteUrl  ' => $labelUseAbsoluteUrl,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getLastDate($optional = array())
  {
    return $this->_request(
        'API.getLastDate',
        array(),
        $optional
    );
  }

  /**
   * Get the result of multiple requests bundled together
   * Take as an argument an array of the API methods to send together
   * For example, ['API.get', 'Action.get', 'DeviceDetection.getType']
   *
   * @param array $methods
   * @param array $optional
   *
   * @return bool
   */
  public function getBulkRequest($methods = array(),
                                 $optional = array())
  {
    $urls = array();

    foreach ($methods as $key => $method) {
      $urls['urls[' . $key . ']'] = urlencode('method=' . $method);
    }

    return $this->_request('API.getBulkRequest', $urls, $optional);
  }

  /**
   * Get suggested values for segments
   *
   * @param string $segmentName
   * @param array  $optional
   *
   * @return bool
   */
  public function getSuggestedValuesForSegment($segmentName, $optional = array())
  {
    return $this->_request(
        'API.getSuggestedValuesForSegment',
        array(
            'segmentName' => $segmentName,
        ),
        $optional
    );
  }

  /**
   * Get actions
   *
   * @param string $segment
   * @param string $columns
   * @param array  $optional
   *
   * @return bool
   */
  public function getAction($segment = '', $columns = '', $optional = array())
  {
    return $this->_request(
        'Actions.get',
        array(
            'segment' => $segment,
            'columns' => $columns,
        ),
        $optional
    );
  }

  /**
   * Get page urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageUrls($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageUrls',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get page URLs after a site search
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageUrlsFollowingSiteSearch($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageUrlsFollowingSiteSearch',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get page titles after a site search
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageTitlesFollowingSiteSearch($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageTitlesFollowingSiteSearch',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get entry page urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getEntryPageUrls($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getEntryPageUrls',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get exit page urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getExitPageUrls($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getExitPageUrls',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get page url information
   *
   * @param string $pageUrl The page url
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageUrl($pageUrl, $segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageUrl',
        array(
            'pageUrl' => $pageUrl,
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get page titles
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageTitles($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageTitles',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get entry page urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getEntryPageTitles($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getEntryPageTitles',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get exit page urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getExitPageTitles($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getExitPageTitles',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get page titles
   *
   * @param string $pageName The page name
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getPageTitle($pageName, $segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getPageTitle',
        array(
            'pageName' => $pageName,
            'segment'  => $segment,
        ),
        $optional
    );
  }

  /**
   * Get downloads
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getDownloads($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getDownloads',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get download information
   *
   * @param string $downloadUrl URL of the download
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getDownload($downloadUrl, $segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getDownload',
        array(
            'downloadUrl' => $downloadUrl,
            'segment'     => $segment,
        ),
        $optional
    );
  }

  /**
   * Get outlinks
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getOutlinks($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getOutlinks',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: ANNOTATIONS
   */

  /**
   * Get outlink information
   *
   * @param string $outlinkUrl URL of the outlink
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getOutlink($outlinkUrl, $segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getOutlink',
        array(
            'outlinkUrl' => $outlinkUrl,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the site search keywords
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSiteSearchKeywords($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getSiteSearchKeywords',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get search keywords with no search results
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSiteSearchNoResultKeywords($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getSiteSearchNoResultKeywords',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get site search categories
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSiteSearchCategories($segment = '', $optional = array())
  {
    return $this->_request(
        'Actions.getSiteSearchCategories',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Add annotation
   *
   * @param string $note
   * @param int    $starred
   * @param array  $optional
   *
   * @return bool
   */
  public function addAnnotation($note, $starred = 0, $optional = array())
  {
    return $this->_request(
        'Annotations.add',
        array(
            'note'    => $note,
            'starred' => $starred,
        ),
        $optional
    );
  }

  /**
   * Save annotation
   *
   * @param int        $idNote
   * @param string     $note
   * @param int|string $starred
   * @param array      $optional
   *
   * @return bool
   */
  public function saveAnnotation($idNote, $note = '', $starred = '', $optional = array())
  {
    return $this->_request(
        'Annotations.save',
        array(
            'idNote'  => $idNote,
            'note'    => $note,
            'starred' => $starred,
        ),
        $optional
    );
  }

  /**
   * Delete annotation
   *
   * @param int   $idNote
   * @param array $optional
   *
   * @return bool
   */
  public function deleteAnnotation($idNote, $optional = array())
  {
    return $this->_request(
        'Annotations.delete',
        array(
            'idNote' => $idNote,
        ),
        $optional
    );
  }

  /**
   * MODULE: CONTENTS
   */

  /**
   * Delete all annotations
   *
   * @param array $optional
   *
   * @return bool
   */
  public function deleteAllAnnotations($optional = array())
  {
    return $this->_request(
        'Annotations.deleteAll',
        array(),
        $optional
    );
  }

  /**
   * Get annotation
   *
   * @param int   $idNote
   * @param array $optional
   *
   * @return bool
   */
  public function getAnnotation($idNote, $optional = array())
  {
    return $this->_request(
        'Annotations.get',
        array(
            'idNote' => $idNote,
        ),
        $optional
    );
  }

  /**
   * MODULE: CUSTOM ALERTS
   */

  /**
   * Get all annotations
   *
   * @param int|string $lastN
   * @param array      $optional
   *
   * @return bool
   */
  public function getAllAnnotation($lastN = '', $optional = array())
  {
    return $this->_request(
        'Annotations.getAll',
        array(
            'lastN' => $lastN,
        ),
        $optional
    );
  }

  /**
   * Get number of annotation for current period
   *
   * @param int    $lastN
   * @param string $getAnnotationText
   * @param array  $optional
   *
   * @return bool
   */
  public function getAnnotationCountForDates($lastN, $getAnnotationText, $optional = array())
  {
    return $this->_request(
        'Annotations.getAnnotationCountForDates',
        array(
            'lastN'             => $lastN,
            'getAnnotationText' => $getAnnotationText,
        ),
        $optional
    );
  }

  /**
   * Get content names
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getContentNames($segment = '', $optional = array())
  {
    return $this->_request(
        'Contents.getContentNames',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get content pieces
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getContentPieces($segment = '', $optional = array())
  {
    return $this->_request(
        'Contents.getContentPieces',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get alert details
   *
   * @param int   $idAlert
   * @param array $optional
   *
   * @return bool
   */
  public function getAlert($idAlert, $optional = array())
  {
    return $this->_request(
        'CustomAlerts.getAlert',
        array(
            'idAlert' => $idAlert,
        ),
        $optional
    );
  }

  /**
   * Get values for alerts in the past
   *
   * @param int   $idAlert
   * @param mixed $subPeriodN
   * @param array $optional
   *
   * @return bool
   */
  public function getValuesForAlertInPast($idAlert, $subPeriodN, $optional = array())
  {
    return $this->_request(
        'CustomAlerts.getValuesForAlertInPast',
        array(
            'idAlert'    => $idAlert,
            'subPeriodN' => $subPeriodN,
        ),
        $optional
    );
  }

  /**
   * Get all alert details
   *
   * @param array      $idSites Array of site IDs
   * @param int|string $ifSuperUserReturnAllAlerts
   * @param array      $optional
   *
   * @return bool
   */
  public function getAlerts($idSites, $ifSuperUserReturnAllAlerts = '', $optional = array())
  {
    return $this->_request(
        'CustomAlerts.getAlerts',
        array(
            'idSites'                    => $idSites,
            'ifSuperUserReturnAllAlerts' => $ifSuperUserReturnAllAlerts,
        ),
        $optional
    );
  }

  /**
   * MODULE: CUSTOM VARIABLES
   * Custom variable information
   */

  /**
   * Add alert
   *
   * @param string $name
   * @param array  $idSites Array of site IDs
   * @param int    $emailMe
   * @param mixed  $additionalEmails
   * @param mixed  $phoneNumbers
   * @param mixed  $metric
   * @param mixed  $metricCondition
   * @param mixed  $metricValue
   * @param mixed  $comparedTo
   * @param mixed  $reportUniqueId
   * @param mixed  $reportCondition
   * @param mixed  $reportValue
   * @param array  $optional
   *
   * @return bool
   */
  public function addAlert($name, $idSites, $emailMe, $additionalEmails, $phoneNumbers, $metric,
                           $metricCondition, $metricValue, $comparedTo, $reportUniqueId, $reportCondition = '',
                           $reportValue = '', $optional = array())
  {
    return $this->_request(
        'CustomAlerts.addAlert',
        array(
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
        ),
        $optional
    );
  }

  /**
   * Edit alert
   *
   * @param int    $idAlert
   * @param string $name
   * @param array  $idSites Array of site IDs
   * @param int    $emailMe
   * @param mixed  $additionalEmails
   * @param mixed  $phoneNumbers
   * @param mixed  $metric
   * @param mixed  $metricCondition
   * @param mixed  $metricValue
   * @param mixed  $comparedTo
   * @param mixed  $reportUniqueId
   * @param mixed  $reportCondition
   * @param mixed  $reportValue
   * @param array  $optional
   *
   * @return bool
   */
  public function editAlert($idAlert, $name, $idSites, $emailMe, $additionalEmails, $phoneNumbers,
                            $metric, $metricCondition, $metricValue, $comparedTo, $reportUniqueId, $reportCondition = '',
                            $reportValue = '', $optional = array())
  {
    return $this->_request(
        'CustomAlerts.editAlert',
        array(
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
        ),
        $optional
    );
  }

  /**
   * MODULE: Dashboard
   */

  /**
   * Delete Alert
   *
   * @param int   $idAlert
   * @param array $optional
   *
   * @return bool
   */
  public function deleteAlert($idAlert, $optional = array())
  {
    return $this->_request(
        'CustomAlerts.deleteAlert',
        array(
            'idAlert' => $idAlert,
        ),
        $optional
    );
  }

  /**
   * MODULE: DEVICES DETECTION
   */

  /**
   * Get triggered alerts
   *
   * @param array $idSites
   * @param array $optional
   *
   * @return bool
   */
  public function getTriggeredAlerts($idSites, $optional = array())
  {
    return $this->_request(
        'CustomAlerts.getTriggeredAlerts',
        array(
            'idSites' => $idSites,
        ),
        $optional
    );
  }

  /**
   * Get custom variables
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCustomVariables($segment = '', $optional = array())
  {
    return $this->_request(
        'CustomVariables.getCustomVariables',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get information about a custom variable
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCustomVariable($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'CustomVariables.getCustomVariablesValuesFromNameId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get list of dashboards
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDashboards($optional = array())
  {
    return $this->_request(
        'Dashboard.getDashboards',
        array(),
        $optional
    );
  }

  /**
   * Get Device Type.
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getDeviceType($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getType',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get Device Brand.
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getDeviceBrand($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getBrand',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get Device Model.
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getDeviceModel($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getModel',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get operating system families
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getOSFamilies($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getOsFamilies',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: EVENTS
   */

  /**
   * Get os versions
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getOsVersions($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getOsVersions',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get browsers
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getBrowsers($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getBrowsers',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get browser versions
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getBrowserVersions($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getBrowserVersions',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get browser engines
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getBrowserEngines($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicesDetection.getBrowserEngines',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get event categories
   *
   * @param string $segment
   * @param string $secondaryDimension ('eventAction' or 'eventName')
   * @param array  $optional
   *
   * @return bool
   */
  public function getEventCategory($segment = '', $secondaryDimension = '', $optional = array())
  {
    return $this->_request(
        'Events.getCategory',
        array(
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ),
        $optional
    );
  }

  /**
   * Get event actions
   *
   * @param string $segment
   * @param string $secondaryDimension ('eventName' or 'eventCategory')
   * @param array  $optional
   *
   * @return bool
   */
  public function getEventAction($segment = '', $secondaryDimension = '', $optional = array())
  {
    return $this->_request(
        'Events.getAction',
        array(
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ),
        $optional
    );
  }

  /**
   * Get event names
   *
   * @param string $segment
   * @param string $secondaryDimension ('eventAction' or 'eventCategory')
   * @param array  $optional
   *
   * @return bool
   */
  public function getEventName($segment = '', $secondaryDimension = '', $optional = array())
  {
    return $this->_request(
        'Events.getName',
        array(
            'segment'            => $segment,
            'secondaryDimension' => $secondaryDimension,
        ),
        $optional
    );
  }

  /**
   * Get action from category ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getActionFromCategoryId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getActionFromCategoryId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get name from category ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNameFromCategoryId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getNameFromCategoryId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: EXAMPLE API
   * Get api and piwiki information
   */

  /**
   * Get category from action ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCategoryFromActionId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getCategoryFromActionId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get name from action ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNameFromActionId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getNameFromActionId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get action from name ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getActionFromNameId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getActionFromNameId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get category from name ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCategoryFromNameId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Events.getCategoryFromNameId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the piwik version
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExamplePiwikVersion($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getPiwikVersion',
        array(),
        $optional
    );
  }

  /**
   * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleAnswerToLife($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getAnswerToLife',
        array(),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleObject($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getObject',
        array(),
        $optional
    );
  }

  /**
   * Get the sum of the parameters
   *
   * @param int|string $a
   * @param int|string $b
   * @param array      $optional
   *
   * @return bool
   */
  public function getExampleSum($a = '0', $b = '0', $optional = array())
  {
    return $this->_request(
        'ExampleAPI.getSum',
        array(
            'a' => $a,
            'b' => $b,
        ),
        $optional
    );
  }

  /**
   * Returns nothing but the success of the request
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleNull($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getNull',
        array(),
        $optional
    );
  }

  /**
   * MODULE: EXAMPLE PLUGIN
   */

  /**
   * Get a short piwik description
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleDescriptionArray($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getDescriptionArray',
        array(),
        $optional
    );
  }

  /**
   * Get a short comparison with other analytic software
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleCompetitionDatatable($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getCompetitionDatatable',
        array(),
        $optional
    );
  }

  /**
   * MODULE: FEEDBACK
   */

  /**
   * Get information about 42
   * http://en.wikipedia.org/wiki/Phrases_from_The_Hitchhiker%27s_Guide_to_the_Galaxy#The_number_42
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleMoreInformationAnswerToLife($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getMoreInformationAnswerToLife',
        array(),
        $optional
    );
  }

  /**
   * MODULE: GOALS
   * Handle goals
   */

  /**
   * Get a multidimensional array
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExampleMultiArray($optional = array())
  {
    return $this->_request(
        'ExampleAPI.getMultiArray',
        array(),
        $optional
    );
  }

  /**
   * Get a multidimensional array
   *
   * @param int   $truth
   * @param array $optional
   *
   * @return bool
   */
  public function getExamplePluginAnswerToLife($truth = 1, $optional = array())
  {
    return $this->_request(
        'ExamplePlugin.getAnswerToLife',
        array(
            'truth' => $truth,
        ),
        $optional
    );
  }

  /**
   * Get a multidimensional array
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getExamplePluginReport($segment = '', $optional = array())
  {
    return $this->_request(
        'ExamplePlugin.getExampleReport',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get a multidimensional array
   *
   * @param string $featureName
   * @param mixed  $like
   * @param string $message
   * @param array  $optional
   *
   * @return bool
   */
  public function sendFeedbackForFeature($featureName, $like, $message = '', $optional = array())
  {
    return $this->_request(
        'Feedback.sendFeedbackForFeature',
        array(
            'featureName' => $featureName,
            'like'        => $like,
            'message'     => $message,
        ),
        $optional
    );
  }

  /**
   * Get all goals
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getGoals($optional = array())
  {
    return $this->_request(
        'Goals.getGoals',
        array(),
        $optional
    );
  }

  /**
   * Add a goal
   *
   * @param string         $name
   * @param string         $matchAttribute
   * @param string         $pattern
   * @param string         $patternType
   * @param boolean|string $caseSensitive
   * @param float|string   $revenue
   * @param boolean|string $allowMultipleConversionsPerVisit
   * @param array          $optional
   *
   * @return bool
   */
  public function addGoal($name, $matchAttribute, $pattern, $patternType, $caseSensitive = '',
                          $revenue = '', $allowMultipleConversionsPerVisit = '', $optional = array())
  {
    return $this->_request(
        'Goals.addGoal',
        array(
            'name'                             => $name,
            'matchAttribute'                   => $matchAttribute,
            'pattern'                          => $pattern,
            'patternType'                      => $patternType,
            'caseSensitive'                    => $caseSensitive,
            'revenue'                          => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ),
        $optional
    );
  }

  /**
   * Update a goal
   *
   * @param int            $idGoal
   * @param string         $name
   * @param string         $matchAttribute
   * @param string         $pattern
   * @param string         $patternType
   * @param boolean|string $caseSensitive
   * @param float|string   $revenue
   * @param boolean|string $allowMultipleConversionsPerVisit
   * @param array          $optional
   *
   * @return bool
   */
  public function updateGoal($idGoal, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = '',
                             $revenue = '', $allowMultipleConversionsPerVisit = '', $optional = array())
  {
    return $this->_request(
        'Goals.updateGoal',
        array(
            'idGoal'                           => $idGoal,
            'name'                             => $name,
            'matchAttribute'                   => $matchAttribute,
            'pattern'                          => $pattern,
            'patternType'                      => $patternType,
            'caseSensitive'                    => $caseSensitive,
            'revenue'                          => $revenue,
            'allowMultipleConversionsPerVisit' => $allowMultipleConversionsPerVisit,
        ),
        $optional
    );
  }

  /**
   * Delete a goal
   *
   * @param int   $idGoal
   * @param array $optional
   *
   * @return bool
   */
  public function deleteGoal($idGoal, $optional = array())
  {
    return $this->_request(
        'Goals.deleteGoal',
        array(
            'idGoal' => $idGoal,
        ),
        $optional
    );
  }

  /**
   * Get the SKU of the items
   *
   * @param boolean $abandonedCarts
   * @param array   $optional
   *
   * @return bool
   */
  public function getItemsSku($abandonedCarts, $optional = array())
  {
    return $this->_request(
        'Goals.getItemsSku',
        array(
            'abandonedCarts' => $abandonedCarts,
        ),
        $optional
    );
  }

  /**
   * Get the name of the items
   *
   * @param boolean $abandonedCarts
   * @param array   $optional
   *
   * @return bool
   */
  public function getItemsName($abandonedCarts, $optional = array())
  {
    return $this->_request(
        'Goals.getItemsName',
        array(
            'abandonedCarts' => $abandonedCarts,
        ),
        $optional
    );
  }

  /**
   * Get the categories of the items
   *
   * @param boolean $abandonedCarts
   * @param array   $optional
   *
   * @return bool
   */
  public function getItemsCategory($abandonedCarts, $optional = array())
  {
    return $this->_request(
        'Goals.getItemsCategory',
        array(
            'abandonedCarts' => $abandonedCarts,
        ),
        $optional
    );
  }

  /**
   * Get conversion rates from a goal
   *
   * @param string     $segment
   * @param int|string $idGoal
   * @param array      $columns
   * @param array      $optional
   *
   * @return bool
   */
  public function getGoal($segment = '', $idGoal = '', $columns = array(),
                          $optional = array())
  {
    return $this->_request(
        'Goals.get',
        array(
            'segment' => $segment,
            'idGoal'  => $idGoal,
            'columns' => $columns,
        ),
        $optional
    );
  }

  /**
   * Get information about a time period and it's conversion rates
   *
   * @param string     $segment
   * @param int|string $idGoal
   * @param array      $optional
   *
   * @return bool
   */
  public function getDaysToConversion($segment = '', $idGoal = '', $optional = array())
  {
    return $this->_request(
        'Goals.getDaysToConversion',
        array(
            'segment' => $segment,
            'idGoal'  => $idGoal,
        ),
        $optional
    );
  }

  /**
   * Get information about how many site visits create a conversion
   *
   * @param string     $segment
   * @param int|string $idGoal
   * @param array      $optional
   *
   * @return bool
   */
  public function getVisitsUntilConversion($segment = '', $idGoal = '', $optional = array())
  {
    return $this->_request(
        'Goals.getVisitsUntilConversion',
        array(
            'segment' => $segment,
            'idGoal'  => $idGoal,
        ),
        $optional
    );
  }

  /**
   * Generate a png report
   *
   * @param string         $apiModule    Module
   * @param string         $apiAction    Action
   * @param                string
   *                                     GRAPH_EVOLUTION
   *                                     GRAPH_VERTICAL_BAR
   *                                     GRAPH_PIE
   *                                     GRAPH_PIE_3D
   * @param int|string     $outputType
   * @param string         $columns
   * @param boolean|string $labels
   * @param int|string     $showLegend
   * @param int|string     $width
   * @param int|string     $height
   * @param int|string     $fontSize
   * @param int|string     $legendFontSize
   * @param boolean|string $aliasedGraph "by default, Graphs are "smooth" (anti-aliased). If you are
   *                                     generating hundreds of graphs and are concerned with performance,
   *                                     you can set aliasedGraph=0. This will disable anti aliasing and
   *                                     graphs will be generated faster, but look less pretty."
   * @param int|string     $idGoal
   * @param array          $colors       Use own colors instead of the default. The colors has to be in hexadecimal
   *                                     value without '#'
   * @param array          $optional
   *
   * @return bool
   */
  public function getImageGraph($apiModule, $apiAction, $graphType = '', $outputType = '0',
                                $columns = '', $labels = '', $showLegend = '1', $width = '', $height = '', $fontSize = '9',
                                $legendFontSize = '', $aliasedGraph = '1', $idGoal = '', $colors = array(),
                                $optional = array())
  {
    return $this->_request(
        'ImageGraph.get',
        array(
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
        ),
        $optional
    );
  }

  /**
   * MODULE: LANGUAGES MANAGER
   * Get plugin insights
   */

  /**
   * Check if piwik can generate insights for current period
   *
   * @param array $optional
   *
   * @return bool
   */
  public function canGenerateInsights($optional = array())
  {
    return $this->_request(
        'Insights.canGenerateInsights',
        array(),
        $optional
    );
  }

  /**
   * Get insights overview
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getInsightsOverview($segment, $optional = array())
  {
    return $this->_request(
        'Insights.getInsightsOverview',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getMoversAndShakersOverview($segment, $optional = array())
  {
    return $this->_request(
        'Insights.getMoversAndShakersOverview',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param int        $reportUniqueId
   * @param string     $segment
   * @param int|string $comparedToXPeriods
   * @param int|string $limitIncreaser
   * @param int|string $limitDecreaser
   * @param array      $optional
   *
   * @return bool
   */
  public function getMoversAndShakers($reportUniqueId, $segment, $comparedToXPeriods = '1',
                                      $limitIncreaser = '4', $limitDecreaser = '4', $optional = array())
  {
    return $this->_request(
        'Insights.getMoversAndShakers',
        array(
            'reportUniqueId'     => $reportUniqueId,
            'segment'            => $segment,
            'comparedToXPeriods' => $comparedToXPeriods,
            'limitIncreaser'     => $limitIncreaser,
            'limitDecreaser'     => $limitDecreaser,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param int        $reportUniqueId
   * @param string     $segment
   * @param int|string $limitIncreaser
   * @param int|string $limitDecreaser
   * @param string     $filterBy
   * @param int|string $minImpactPercent (0-100)
   * @param int|string $minGrowthPercent (0-100)
   * @param int|string $comparedToXPeriods
   * @param string     $orderBy
   * @param array      $optional
   *
   * @return bool
   */
  public function getInsights($reportUniqueId, $segment, $limitIncreaser = '5', $limitDecreaser = '5',
                              $filterBy = '', $minImpactPercent = '2', $minGrowthPercent = '20', $comparedToXPeriods = '1',
                              $orderBy = 'absolute', $optional = array())
  {
    return $this->_request(
        'Insights.getInsights',
        array(
            'reportUniqueId'     => $reportUniqueId,
            'segment'            => $segment,
            'limitIncreaser'     => $limitIncreaser,
            'limitDecreaser'     => $limitDecreaser,
            'filterBy'           => $filterBy,
            'minImpactPercent'   => $minImpactPercent,
            'minGrowthPercent'   => $minGrowthPercent,
            'comparedToXPeriods' => $comparedToXPeriods,
            'orderBy'            => $orderBy,
        ),
        $optional
    );
  }

  /**
   * MODULE: LANGUAGES MANAGER
   * Manage languages
   */

  /**
   * Proof if language is available
   *
   * @param string $languageCode
   * @param array  $optional
   *
   * @return bool
   */
  public function getLanguageAvailable($languageCode, $optional = array())
  {
    return $this->_request(
        'LanguagesManager.isLanguageAvailable',
        array(
            'languageCode' => $languageCode,
        ),
        $optional
    );
  }

  /**
   * Get all available languages
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAvailableLanguages($optional = array())
  {
    return $this->_request(
        'LanguagesManager.getAvailableLanguages',
        array(),
        $optional
    );
  }

  /**
   * Get all available languages with information
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAvailableLanguagesInfo($optional = array())
  {
    return $this->_request(
        'LanguagesManager.getAvailableLanguagesInfo',
        array(),
        $optional
    );
  }

  /**
   * Get all available languages with their names
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAvailableLanguageNames($optional = array())
  {
    return $this->_request(
        'LanguagesManager.getAvailableLanguageNames',
        array(),
        $optional
    );
  }

  /**
   * Get translations for a language
   *
   * @param string $languageCode
   * @param array  $optional
   *
   * @return bool
   */
  public function getTranslations($languageCode, $optional = array())
  {
    return $this->_request(
        'LanguagesManager.getTranslationsForLanguage',
        array(
            'languageCode' => $languageCode,
        ),
        $optional
    );
  }

  /**
   * Get the language for the user with the login $login
   *
   * @param string $login
   * @param array  $optional
   *
   * @return bool
   */
  public function getLanguageForUser($login, $optional = array())
  {
    return $this->_request(
        'LanguagesManager.getLanguageForUser',
        array(
            'login' => $login,
        ),
        $optional
    );
  }

  /**
   * Set the language for the user with the login $login
   *
   * @param string $login
   * @param string $languageCode
   * @param array  $optional
   *
   * @return bool
   */
  public function setLanguageForUser($login, $languageCode, $optional = array())
  {
    return $this->_request(
        'LanguagesManager.setLanguageForUser',
        array(
            'login'        => $login,
            'languageCode' => $languageCode,
        ),
        $optional
    );
  }


  /**
   * MODULE: LIVE
   * Request live data
   */

  /**
   * Get a short information about the visit counts in the last minutes
   *
   * @param int    $lastMinutes Default: 60
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCounters($lastMinutes = 60, $segment = '', $optional = array())
  {
    return $this->_request(
        'Live.getCounters',
        array(
            'lastMinutes' => $lastMinutes,
            'segment'     => $segment,
        ),
        $optional
    );
  }

  /**
   * Get information about the last visits
   *
   * @param string $segment
   * @param string $minTimestamp
   * @param string $doNotFetchActions
   * @param array  $optional
   *
   * @return bool
   */
  public function getLastVisitsDetails($segment = '', $minTimestamp = '', $doNotFetchActions = '', $optional = array())
  {
    return $this->_request(
        'Live.getLastVisitsDetails',
        array(
            'segment'           => $segment,
            'minTimestamp'      => $minTimestamp,
            'doNotFetchActions' => $doNotFetchActions,
        ),
        $optional
    );
  }

  /**
   * Get a profile for a visitor
   *
   * @param int|string $visitorId
   * @param string     $segment
   * @param array      $optional
   *
   * @return bool
   */
  public function getVisitorProfile($visitorId = '', $segment = '', $optional = array())
  {
    return $this->_request(
        'Live.getVisitorProfile',
        array(
            'visitorId' => $visitorId,
            'segment'   => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the ID of the most recent visitor
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getMostRecentVisitorId($segment = '', $optional = array())
  {
    return $this->_request(
        'Live.getMostRecentVisitorId',
        array(
            'segment' => $segment,
        ),
        $optional
    );
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
   *
   * @return bool
   */
  public function areSMSAPICredentialProvided($optional = array())
  {
    return $this->_request(
        'MobileMessaging.areSMSAPICredentialProvided',
        array(),
        $optional
    );
  }

  /**
   * Get list with sms provider
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSMSProvider($optional = array())
  {
    return $this->_request(
        'MobileMessaging.getSMSProvider',
        array(),
        $optional
    );
  }

  /**
   * Set SMSAPI credentials
   *
   * @param string $provider
   * @param string $apiKey
   * @param array  $optional
   *
   * @return bool
   */
  public function setSMSAPICredential($provider, $apiKey, $optional = array())
  {
    return $this->_request(
        'MobileMessaging.setSMSAPICredential',
        array(
            'provider' => $provider,
            'apiKey'   => $apiKey,
        ),
        $optional
    );
  }

  /**
   * Add phone number
   *
   * @param string $phoneNumber
   * @param array  $optional
   *
   * @return bool
   */
  public function addPhoneNumber($phoneNumber, $optional = array())
  {
    return $this->_request(
        'MobileMessaging.addPhoneNumber',
        array(
            'phoneNumber' => $phoneNumber,
        ),
        $optional
    );
  }

  /**
   * Get credits left
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getCreditLeft($optional = array())
  {
    return $this->_request(
        'MobileMessaging.getCreditLeft',
        array(),
        $optional
    );
  }

  /**
   * Remove phone number
   *
   * @param string $phoneNumber
   * @param array  $optional
   *
   * @return bool
   */
  public function removePhoneNumber($phoneNumber, $optional = array())
  {
    return $this->_request(
        'MobileMessaging.removePhoneNumber',
        array(
            'phoneNumber' => $phoneNumber,
        ),
        $optional
    );
  }

  /**
   * Validate phone number
   *
   * @param string $phoneNumber
   * @param string $verificationCode
   * @param array  $optional
   *
   * @return bool
   */
  public function validatePhoneNumber($phoneNumber, $verificationCode, $optional = array())
  {
    return $this->_request(
        'MobileMessaging.validatePhoneNumber',
        array(
            'phoneNumber'      => $phoneNumber,
            'verificationCode' => $verificationCode,
        ),
        $optional
    );
  }

  /**
   * Delete SMSAPI credentials
   *
   * @param array $optional
   *
   * @return bool
   */
  public function deleteSMSAPICredential($optional = array())
  {
    return $this->_request(
        'MobileMessaging.deleteSMSAPICredential',
        array(),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param       $delegatedManagement
   * @param array $optional
   *
   * @return bool
   */
  public function setDelegatedManagement($delegatedManagement, $optional = array())
  {
    return $this->_request(
        'MobileMessaging.setDelegatedManagement',
        array(
            'delegatedManagement' => $delegatedManagement,
        ),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDelegatedManagement($optional = array())
  {
    return $this->_request(
        'MobileMessaging.getDelegatedManagement',
        array(),
        $optional
    );
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
   * @param array  $optional
   *
   * @return bool
   */
  public function getMultiSites($segment = '', $enhanced = '', $optional = array())
  {
    return $this->_request(
        'MultiSites.getAll',
        array(
            'segment'  => $segment,
            'enhanced' => $enhanced,
        ),
        $optional
    );
  }

  /**
   * Get key metrics about one of the sites the user manages
   *
   * @param string $segment
   * @param string $enhanced
   * @param array  $optional
   *
   * @return bool
   */
  public function getOne($segment = '', $enhanced = '', $optional = array())
  {
    return $this->_request(
        'MultiSites.getOne',
        array(
            'segment'  => $segment,
            'enhanced' => $enhanced,
        ),
        $optional
    );
  }

  /**
   * MODULE: OVERLAY
   */

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getOverlayTranslations($optional = array())
  {
    return $this->_request(
        'Overlay.getTranslations',
        array(),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getOverlayExcludedQueryParameters($optional = array())
  {
    return $this->_request(
        'Overlay.getExcludedQueryParameters',
        array(),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getOverlayFollowingPages($segment = '', $optional = array())
  {
    return $this->_request(
        'Overlay.getFollowingPages',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: PROVIDER
   * Get provider information
   */

  /**
   * Get information about visitors internet providers
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getProvider($segment = '', $optional = array())
  {
    return $this->_request(
        'Provider.getProvider',
        array(
            'segment' => $segment,
        ),
        $optional
    );
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
   * @param array  $optional
   *
   * @return bool
   */
  public function getReferrerType($segment = '', $typeReferrer = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getReferrerType',
        array(
            'segment'      => $segment,
            'typeReferrer' => $typeReferrer,
        ),
        $optional
    );
  }

  /**
   * Get all referrers
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAllReferrers($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getAll',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get referrer keywords
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getKeywords($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getKeywords',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get keywords for an url
   *
   * @param string $url
   * @param array  $optional
   *
   * @return bool
   */
  public function getKeywordsForPageUrl($url, $optional = array())
  {
    return $this->_request(
        'Referrers.getKeywordsForPageUrl',
        array(
            'url' => $url,
        ),
        $optional
    );
  }

  /**
   * Get keywords for an page title
   *
   * @param string $title
   * @param array  $optional
   *
   * @return bool
   */
  public function getKeywordsForPageTitle($title, $optional = array())
  {
    return $this->_request(
        'Referrers.getKeywordsForPageTitle',
        array(
            'title' => $title,
        ),
        $optional
    );
  }

  /**
   * Get search engines by keyword
   *
   * @param int|string $idSubtable
   * @param string     $segment
   * @param array      $optional
   *
   * @return bool
   */
  public function getSearchEnginesFromKeywordId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getSearchEnginesFromKeywordId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get search engines
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSearchEngines($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getSearchEngines',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get search engines by search engine ID
   *
   * @param int|string $idSubtable
   * @param string     $segment
   * @param array      $optional
   *
   * @return bool
   */
  public function getKeywordsFromSearchEngineId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getKeywordsFromSearchEngineId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get campaigns
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCampaigns($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getCampaigns',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get keywords by campaign ID
   *
   * @param int|string $idSubtable
   * @param string     $segment
   * @param array      $optional
   *
   * @return bool
   */
  public function getKeywordsFromCampaignId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getKeywordsFromCampaignId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get name
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingName($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getName',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get keyword content from name id
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingKeywordContentFromNameId($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getKeywordContentFromNameId',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get keyword
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingKeyword($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getKeyword',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get source   *
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingSource($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getSource',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get medium
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingMedium($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getMedium',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get content
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingContent($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getContent',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get source and medium
   * from advanced campaign reporting
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingSourceMedium($segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getSourceMedium',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get name from source and medium by ID
   * from advanced campaign reporting
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getAdvancedCampaignReportingNameFromSourceMediumId($idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'AdvancedCampaignReporting.getNameFromSourceMediumId',
        array(
            'idSubtable' => $idSubtable,
            'segment'    => $segment,
        ),
        $optional
    );
  }

  /**
   * Get website referrerals
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getWebsites($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getWebsites',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get urls by website ID
   *
   * @param int    $idSubtable
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUrlsFromWebsiteId(/** @noinspection PhpUnusedParameterInspection */
      $idSubtable, $segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getUrlsFromWebsiteId',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get social referrerals
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSocials($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getSocials',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get social referral urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUrlsForSocial($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getUrlsForSocial',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of distinct search engines
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfSearchEngines($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getNumberOfDistinctSearchEngines',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of distinct keywords
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfKeywords($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getNumberOfDistinctKeywords',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of distinct campaigns
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfCampaigns($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getNumberOfDistinctCampaigns',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of distinct websites
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfWebsites($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getNumberOfDistinctWebsites',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of distinct websites urls
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfWebsitesUrls($segment = '', $optional = array())
  {
    return $this->_request(
        'Referrers.getNumberOfDistinctWebsitesUrls',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: SEO
   * Get SEO information
   */

  /**
   * Get the SEO rank of an url
   *
   * @param string $url
   * @param array  $optional
   *
   * @return bool
   */
  public function getSeoRank($url, $optional = array())
  {
    return $this->_request(
        'SEO.getRank',
        array(
            'url' => $url,
        ),
        $optional
    );
  }

  /**
   * MODULE: SCHEDULED REPORTS
   * Manage pdf reports
   */

  /**
   * Add scheduled report
   *
   * @param string     $description
   * @param string     $period
   * @param string     $hour
   * @param string     $reportType
   * @param string     $reportFormat
   * @param array      $reports
   * @param string     $parameters
   * @param int|string $idSegment
   * @param array      $optional
   *
   * @return bool
   */
  public function addReport($description, $period, $hour, $reportType, $reportFormat, $reports,
                            $parameters, $idSegment = '', $optional = array())
  {
    return $this->_request(
        'ScheduledReports.addReport',
        array(
            'description'  => $description,
            'period'       => $period,
            'hour'         => $hour,
            'reportType'   => $reportType,
            'reportFormat' => $reportFormat,
            'reports'      => $reports,
            'parameters'   => $parameters,
            'idSegment'    => $idSegment,
        ),
        $optional
    );
  }

  /**
   * Updated scheduled report
   *
   * @param int        $idReport
   * @param string     $description
   * @param string     $period
   * @param string     $hour
   * @param string     $reportType
   * @param string     $reportFormat
   * @param array      $reports
   * @param string     $parameters
   * @param int|string $idSegment
   * @param array      $optional
   *
   * @return bool
   */
  public function updateReport($idReport, $description, $period, $hour, $reportType, $reportFormat,
                               $reports, $parameters, $idSegment = '', $optional = array())
  {
    return $this->_request(
        'ScheduledReports.updateReport',
        array(
            'idReport'     => $idReport,
            'description'  => $description,
            'period'       => $period,
            'hour'         => $hour,
            'reportType'   => $reportType,
            'reportFormat' => $reportFormat,
            'reports'      => $reports,
            'parameters'   => $parameters,
            'idSegment'    => $idSegment,
        ),
        $optional
    );
  }

  /**
   * Delete scheduled report
   *
   * @param int   $idReport
   * @param array $optional
   *
   * @return bool
   */
  public function deleteReport($idReport, $optional = array())
  {
    return $this->_request(
        'ScheduledReports.deleteReport',
        array(
            'idReport' => $idReport,
        ),
        $optional
    );
  }

  /**
   * Get list of scheduled reports
   *
   * @param int|string $idReport
   * @param int|string $ifSuperUserReturnOnlySuperUserReports
   * @param int|string $idSegment
   * @param array      $optional
   *
   * @return bool
   */
  public function getReports($idReport = '', $ifSuperUserReturnOnlySuperUserReports = '',
                             $idSegment = '', $optional = array())
  {
    return $this->_request(
        'ScheduledReports.getReports',
        array(
            'idReport'                              => $idReport,
            'ifSuperUserReturnOnlySuperUserReports' => $ifSuperUserReturnOnlySuperUserReports,
            'idSegment'                             => $idSegment,
        ),
        $optional
    );
  }

  /**
   * Get list of scheduled reports
   *
   * @param int          $idReport
   * @param int|string   $language
   * @param int|string   $outputType
   * @param string       $reportFormat
   * @param array|string $parameters
   * @param array        $optional
   *
   * @return bool
   */
  public function generateReport($idReport, $language = '', $outputType = '', $reportFormat = '',
                                 $parameters = '', $optional = array())
  {
    return $this->_request(
        'ScheduledReports.generateReport',
        array(
            'idReport'     => $idReport,
            'language'     => $language,
            'outputType'   => $outputType,
            'reportFormat' => $reportFormat,
            'parameters'   => $parameters,
        ),
        $optional
    );
  }

  /**
   * Send scheduled reports
   *
   * @param int        $idReport
   * @param int|string $force
   * @param array      $optional
   *
   * @return bool
   */
  public function sendReport($idReport, $force = '', $optional = array())
  {
    return $this->_request(
        'ScheduledReports.sendReport',
        array(
            'idReport' => $idReport,
            'force'    => $force,
        ),
        $optional
    );
  }

  /**
   * MODULE: SEGMENT EDITOR
   */

  /**
   * Check if current user can add new segments
   *
   * @param array $optional
   *
   * @return bool
   */
  public function isUserCanAddNewSegment($optional = array())
  {
    return $this->_request(
        'SegmentEditor.isUserCanAddNewSegment',
        array(),
        $optional
    );
  }

  /**
   * Delete a segment
   *
   * @param int   $idSegment
   * @param array $optional
   *
   * @return bool
   */
  public function deleteSegment($idSegment, $optional = array())
  {
    return $this->_request(
        'SegmentEditor.delete',
        array(
            'idSegment' => $idSegment,
        ),
        $optional
    );
  }

  /**
   * Updates a segment
   *
   * @param int        $idSegment
   * @param string     $name
   * @param string     $definition
   * @param int|string $autoArchive
   * @param int|string $enableAllUsers
   * @param array      $optional
   *
   * @return bool
   */
  public function updateSegment($idSegment, $name, $definition, $autoArchive = '',
                                $enableAllUsers = '', $optional = array())
  {
    return $this->_request(
        'SegmentEditor.update',
        array(
            'idSegment'      => $idSegment,
            'name'           => $name,
            'definition'     => $definition,
            'autoArchive'    => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ),
        $optional
    );
  }

  /**
   * Updates a segment
   *
   * @param string     $name
   * @param string     $definition
   * @param int|string $autoArchive
   * @param int|string $enableAllUsers
   * @param array      $optional
   *
   * @return bool
   */
  public function addSegment($name, $definition, $autoArchive = '', $enableAllUsers = '', $optional = array())
  {
    return $this->_request(
        'SegmentEditor.add',
        array(
            'name'           => $name,
            'definition'     => $definition,
            'autoArchive'    => $autoArchive,
            'enableAllUsers' => $enableAllUsers,
        ),
        $optional
    );
  }

  /**
   * Get a segment
   *
   * @param int   $idSegment
   * @param array $optional
   *
   * @return bool
   */
  public function getSegment($idSegment, $optional = array())
  {
    return $this->_request(
        'SegmentEditor.get',
        array(
            'idSegment' => $idSegment,
        ),
        $optional
    );
  }

  /**
   * Get all segments
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAllSegments($optional = array())
  {
    return $this->_request(
        'SegmentEditor.getAll',
        array(),
        $optional
    );
  }

  /**
   * MODULE: SITES MANAGER
   * Manage sites
   */

  /**
   * Get the JS tag of the current site
   *
   * @param string     $piwikUrl
   * @param int|string $mergeSubdomains
   * @param int|string $groupPageTitlesByDomain
   * @param int|string $mergeAliasUrls
   * @param int|string $visitorCustomVariables
   * @param int|string $pageCustomVariables
   * @param int|string $customCampaignNameQueryParam
   * @param int|string $customCampaignKeywordParam
   * @param int|string $doNotTrack
   * @param int|string $disableCookies
   * @param array      $optional
   *
   * @return bool
   */
  public function getJavascriptTag($piwikUrl, $mergeSubdomains = '', $groupPageTitlesByDomain = '',
                                   $mergeAliasUrls = '', $visitorCustomVariables = '', $pageCustomVariables = '',
                                   $customCampaignNameQueryParam = '', $customCampaignKeywordParam = '', $doNotTrack = '',
                                   $disableCookies = '', $optional = array())
  {
    return $this->_request(
        'SitesManager.getJavascriptTag',
        array(
            'piwikUrl'                     => $piwikUrl,
            'mergeSubdomains'              => $mergeSubdomains,
            'groupPageTitlesByDomain'      => $groupPageTitlesByDomain,
            'mergeAliasUrls'               => $mergeAliasUrls,
            'visitorCustomVariables'       => $visitorCustomVariables,
            'pageCustomVariables'          => $pageCustomVariables,
            'customCampaignNameQueryParam' => $customCampaignNameQueryParam,
            'customCampaignKeywordParam'   => $customCampaignKeywordParam,
            'doNotTrack'                   => $doNotTrack,
            'disableCookies'               => $disableCookies,
        ),
        $optional
    );
  }

  /**
   * Get image tracking code of the current site
   *
   * @param string     $piwikUrl
   * @param int|string $actionName
   * @param int|string $idGoal
   * @param int|string $revenue
   * @param array      $optional
   *
   * @return bool
   */
  public function getImageTrackingCode($piwikUrl, $actionName = '', $idGoal = '',
                                       $revenue = '', $optional = array())
  {
    return $this->_request(
        'SitesManager.getImageTrackingCode',
        array(
            'piwikUrl'   => $piwikUrl,
            'actionName' => $actionName,
            'idGoal'     => $idGoal,
            'revenue'    => $revenue,
        ),
        $optional
    );
  }

  /**
   * Get sites from a group
   *
   * @param string $group
   * @param array  $optional
   *
   * @return bool
   */
  public function getSitesFromGroup($group, $optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesFromGroup',
        array(
            'group' => $group,
        ),
        $optional
    );
  }

  /**
   * Get all site groups
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesGroups($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesGroups',
        array(),
        $optional
    );
  }

  /**
   * Get information about the current site
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSiteInformation($optional = array())
  {
    return $this->_request(
        'SitesManager.getSiteFromId',
        array(),
        $optional
    );
  }

  /**
   * Get urls from current site
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSiteUrls($optional = array())
  {
    return $this->_request(
        'SitesManager.getSiteUrlsFromId',
        array(),
        $optional
    );
  }

  /**
   * Get all sites
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAllSites($optional = array())
  {
    return $this->_request(
        'SitesManager.getAllSites',
        array(),
        $optional
    );
  }

  /**
   * Get all sites with ID
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getAllSitesId($optional = array())
  {
    return $this->_request(
        'SitesManager.getAllSitesId',
        array(),
        $optional
    );
  }

  /**
   * Get all sites with the visit count since $timestamp
   *
   * @param string $timestamp
   * @param array  $optional
   *
   * @return bool
   */
  public function getSitesIdWithVisits($timestamp, $optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesIdWithVisits',
        array(
            'timestamp' => $timestamp,
        ),
        $optional
    );
  }

  /**
   * Get all sites where the current user has admin access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesWithAdminAccess($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesWithAdminAccess',
        array(),
        $optional
    );
  }

  /**
   * Get all sites where the current user has view access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesWithViewAccess($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesWithViewAccess',
        array(),
        $optional
    );
  }

  /**
   * Get all sites where the current user has a least view access
   *
   * @param int|string $limit
   * @param array      $optional
   *
   * @return bool
   */
  public function getSitesWithAtLeastViewAccess($limit = '', $optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesWithAtLeastViewAccess',
        array(
            'limit' => $limit,
        ),
        $optional
    );
  }

  /**
   * Get all sites with ID where the current user has admin access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesIdWithAdminAccess($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesIdWithAdminAccess',
        array(),
        $optional
    );
  }

  /**
   * Get all sites with ID where the current user has view access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesIdWithViewAccess($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesIdWithViewAccess',
        array(),
        $optional
    );
  }

  /**
   * Get all sites with ID where the current user has at least view access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSitesIdWithAtLeastViewAccess($optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesIdWithAtLeastViewAccess',
        array(),
        $optional
    );
  }

  /**
   * Get a site by it's URL
   *
   * @param string $url
   * @param array  $optional
   *
   * @return bool
   */
  public function getSitesIdFromSiteUrl($url, $optional = array())
  {
    return $this->_request(
        'SitesManager.getSitesIdFromSiteUrl',
        array(
            'url' => $url,
        ),
        $optional
    );
  }

  /**
   * Add a site
   *
   * @param string         $siteName
   * @param array          $urls
   * @param boolean|string $ecommerce
   * @param boolean|string $siteSearch
   * @param string         $searchKeywordParameters
   * @param string         $searchCategoryParameters
   * @param array|string   $excludeIps
   * @param array|string   $excludedQueryParameters
   * @param string         $timezone
   * @param string         $currency
   * @param string         $group
   * @param string         $startDate
   * @param string         $excludedUserAgents
   * @param string         $keepURLFragments
   * @param string         $type
   * @param array          $optional
   *
   * @return bool
   */
  public function addSite($siteName, $urls, $ecommerce = '', $siteSearch = '',
                          $searchKeywordParameters = '', $searchCategoryParameters = '', $excludeIps = '',
                          $excludedQueryParameters = '', $timezone = '', $currency = '', $group = '', $startDate = '',
                          $excludedUserAgents = '', $keepURLFragments = '', $type = '', $optional = array())
  {
    return $this->_request(
        'SitesManager.addSite',
        array(
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
        ),
        $optional
    );
  }

  /**
   * Delete current site
   *
   * @param array $optional
   *
   * @return bool
   */
  public function deleteSite($optional = array())
  {
    return $this->_request(
        'SitesManager.deleteSite',
        array(),
        $optional
    );
  }

  /**
   * Add alias urls for the current site
   *
   * @param array $urls
   * @param array $optional
   *
   * @return bool
   */
  public function addSiteAliasUrls($urls, $optional = array())
  {
    return $this->_request(
        'SitesManager.addSiteAliasUrls',
        array(
            'urls' => $urls,
        ),
        $optional
    );
  }

  /**
   * Set alias urls for the current site
   *
   * @param array $urls
   * @param array $optional
   *
   * @return bool
   */
  public function setSiteAliasUrls($urls, $optional = array())
  {
    return $this->_request(
        'SitesManager.setSiteAliasUrls',
        array(
            'urls' => $urls,
        ),
        $optional
    );
  }

  /**
   * Get IP's for a specific range
   *
   * @param string $ipRange
   * @param array  $optional
   *
   * @return bool
   */
  public function getIpsForRange($ipRange, $optional = array())
  {
    return $this->_request(
        'SitesManager.getIpsForRange',
        array(
            'ipRange' => $ipRange,
        ),
        $optional
    );
  }

  /**
   * Set the global excluded IP's
   *
   * @param array $excludedIps
   * @param array $optional
   *
   * @return bool
   */
  public function setExcludedIps($excludedIps, $optional = array())
  {
    return $this->_request(
        'SitesManager.setGlobalExcludedIps',
        array(
            'excludedIps' => $excludedIps,
        ),
        $optional
    );
  }

  /**
   * Set global search parameters
   *
   * @param       $searchKeywordParameters
   * @param       $searchCategoryParameters
   * @param array $optional
   *
   * @return bool
   */
  public function setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters, $optional = array())
  {
    return $this->_request(
        'SitesManager.setGlobalSearchParameters ',
        array(
            'searchKeywordParameters'  => $searchKeywordParameters,
            'searchCategoryParameters' => $searchCategoryParameters,
        ),
        $optional
    );
  }

  /**
   * Get search keywords
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSearchKeywordParametersGlobal($optional = array())
  {
    return $this->_request(
        'SitesManager.getSearchKeywordParametersGlobal',
        array(),
        $optional
    );
  }

  /**
   * Get search categories
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getSearchCategoryParametersGlobal($optional = array())
  {
    return $this->_request(
        'SitesManager.getSearchCategoryParametersGlobal',
        array(),
        $optional
    );
  }

  /**
   * Get the global excluded query parameters
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExcludedParameters($optional = array())
  {
    return $this->_request(
        'SitesManager.getExcludedQueryParametersGlobal',
        array(),
        $optional
    );
  }

  /**
   * Get the global excluded user agents
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExcludedUserAgentsGlobal($optional = array())
  {
    return $this->_request(
        'SitesManager.getExcludedUserAgentsGlobal',
        array(),
        $optional
    );
  }

  /**
   * Set the global excluded user agents
   *
   * @param array $excludedUserAgents
   * @param array $optional
   *
   * @return bool
   */
  public function setGlobalExcludedUserAgents($excludedUserAgents, $optional = array())
  {
    return $this->_request(
        'SitesManager.setGlobalExcludedUserAgents',
        array(
            'excludedUserAgents' => $excludedUserAgents,
        ),
        $optional
    );
  }

  /**
   * Check if site specific user agent exclude is enabled
   *
   * @param array $optional
   *
   * @return bool
   */
  public function isSiteSpecificUserAgentExcludeEnabled($optional = array())
  {
    return $this->_request(
        'SitesManager.isSiteSpecificUserAgentExcludeEnabled',
        array(),
        $optional
    );
  }

  /**
   * Set site specific user agent exclude
   *
   * @param int   $enabled
   * @param array $optional
   *
   * @return bool
   */
  public function setSiteSpecificUserAgentExcludeEnabled($enabled, $optional = array())
  {
    return $this->_request(
        'SitesManager.setSiteSpecificUserAgentExcludeEnabled',
        array(
            'enabled' => $enabled,
        ),
        $optional
    );
  }

  /**
   * Check if the url fragments should be global
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getKeepURLFragmentsGlobal($optional = array())
  {
    return $this->_request(
        'SitesManager.getKeepURLFragmentsGlobal',
        array(),
        $optional
    );
  }

  /**
   * Set the url fragments global
   *
   * @param int   $enabled
   * @param array $optional
   *
   * @return bool
   */
  public function setKeepURLFragmentsGlobal($enabled, $optional = array())
  {
    return $this->_request(
        'SitesManager.setKeepURLFragmentsGlobal',
        array(
            'enabled' => $enabled,
        ),
        $optional
    );
  }

  /**
   * Set the global excluded query parameters
   *
   * @param array $excludedQueryParameters
   * @param array $optional
   *
   * @return bool
   */
  public function setExcludedParameters($excludedQueryParameters, $optional = array())
  {
    return $this->_request(
        'SitesManager.setGlobalExcludedQueryParameters',
        array(
            'excludedQueryParameters' => $excludedQueryParameters,
        ),
        $optional
    );
  }

  /**
   * Get the global excluded IP's
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getExcludedIps($optional = array())
  {
    return $this->_request(
        'SitesManager.getExcludedIpsGlobal',
        array(),
        $optional
    );
  }


  /**
   * Get the default currency
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultCurrency($optional = array())
  {
    return $this->_request(
        'SitesManager.getDefaultCurrency',
        array(),
        $optional
    );
  }

  /**
   * Set the default currency
   *
   * @param string $defaultCurrency
   * @param array  $optional
   *
   * @return bool
   */
  public function setDefaultCurrency($defaultCurrency, $optional = array())
  {
    return $this->_request(
        'SitesManager.setDefaultCurrency',
        array(
            'defaultCurrency' => $defaultCurrency,
        ),
        $optional
    );
  }


  /**
   * Get the default timezone
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getDefaultTimezone($optional = array())
  {
    return $this->_request(
        'SitesManager.getDefaultTimezone',
        array(),
        $optional
    );
  }

  /**
   * Set the default timezone
   *
   * @param string $defaultTimezone
   * @param array  $optional
   *
   * @return bool
   */
  public function setDefaultTimezone($defaultTimezone, $optional = array())
  {
    return $this->_request(
        'SitesManager.setDefaultTimezone',
        array(
            'defaultTimezone' => $defaultTimezone,
        ),
        $optional
    );
  }

  /**
   * Update current site
   *
   * @param string         $siteName
   * @param array          $urls
   * @param boolean|string $ecommerce
   * @param boolean|string $siteSearch
   * @param string         $searchKeywordParameters
   * @param string         $searchCategoryParameters
   * @param array|string   $excludeIps
   * @param array|string   $excludedQueryParameters
   * @param string         $timezone
   * @param string         $currency
   * @param string         $group
   * @param string         $startDate
   * @param string         $excludedUserAgents
   * @param string         $keepURLFragments
   * @param string         $type
   * @param string         $settings
   * @param array          $optional
   *
   * @return bool
   */
  public function updateSite($siteName, $urls, $ecommerce = '', $siteSearch = '',
                             $searchKeywordParameters = '', $searchCategoryParameters = '', $excludeIps = '',
                             $excludedQueryParameters = '', $timezone = '', $currency = '', $group = '', $startDate = '',
                             $excludedUserAgents = '', $keepURLFragments = '', $type = '', $settings = '', $optional = array())
  {
    return $this->_request(
        'SitesManager.updateSite',
        array(
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
        ),
        $optional
    );
  }

  /**
   * Get a list with all available currencies
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getCurrencyList($optional = array())
  {
    return $this->_request(
        'SitesManager.getCurrencyList',
        array(),
        $optional
    );
  }

  /**
   * Get a list with all currency symbols
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getCurrencySymbols($optional = array())
  {
    return $this->_request(
        'SitesManager.getCurrencySymbols',
        array(),
        $optional
    );
  }

  /**
   * Get a list with available timezones
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getTimezonesList($optional = array())
  {
    return $this->_request(
        'SitesManager.getTimezonesList',
        array(),
        $optional
    );
  }

  /**
   * Unknown
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getUniqueSiteTimezones($optional = array())
  {
    return $this->_request(
        'SitesManager.getUniqueSiteTimezones',
        array(),
        $optional
    );
  }

  /**
   * Rename group
   *
   * @param string $oldGroupName
   * @param string $newGroupName
   * @param array  $optional
   *
   * @return bool
   */
  public function renameGroup($oldGroupName, $newGroupName, $optional = array())
  {
    return $this->_request(
        'SitesManager.renameGroup',
        array(
            'oldGroupName' => $oldGroupName,
            'newGroupName' => $newGroupName,
        ),
        $optional
    );
  }

  /**
   * Get all sites which matches the pattern
   *
   * @param string $pattern
   * @param array  $optional
   *
   * @return bool
   */
  public function getPatternMatchSites($pattern, $optional = array())
  {
    return $this->_request(
        'SitesManager.getPatternMatchSites',
        array(
            'pattern' => $pattern,
        ),
        $optional
    );
  }

  /**
   * MODULE: TRANSITIONS
   * Get transitions for page URLs, titles and actions
   */

  /**
   * Get transitions for a page title
   *
   * @param        $pageTitle
   * @param string $segment
   * @param string $limitBeforeGrouping
   * @param array  $optional
   *
   * @return bool
   */
  public function getTransitionsForPageTitle($pageTitle, $segment = '', $limitBeforeGrouping = '', $optional = array())
  {
    return $this->_request(
        'Transitions.getTransitionsForPageTitle',
        array(
            'pageTitle'           => $pageTitle,
            'segment'             => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ),
        $optional
    );
  }

  /**
   * Get transitions for a page URL
   *
   * @param        $pageUrl
   * @param string $segment
   * @param string $limitBeforeGrouping
   * @param array  $optional
   *
   * @return bool
   */
  public function getTransitionsForPageUrl($pageUrl, $segment = '', $limitBeforeGrouping = '', $optional = array())
  {
    return $this->_request(
        'Transitions.getTransitionsForPageTitle',
        array(
            'pageUrl'             => $pageUrl,
            'segment'             => $segment,
            'limitBeforeGrouping' => $limitBeforeGrouping,
        ),
        $optional
    );
  }

  /**
   * Get transitions for a page URL
   *
   * @param             $actionName
   * @param             $actionType
   * @param string      $segment
   * @param string      $limitBeforeGrouping
   * @param string      $parts
   * @param bool|string $returnNormalizedUrls
   * @param array       $optional
   *
   * @return bool
   */
  public function getTransitionsForAction($actionName, $actionType, $segment = '',
                                          $limitBeforeGrouping = '', $parts = 'all', $returnNormalizedUrls = '', $optional = array())
  {
    return $this->_request(
        'Transitions.getTransitionsForAction',
        array(
            'actionName'           => $actionName,
            'actionType'           => $actionType,
            'segment'              => $segment,
            'limitBeforeGrouping'  => $limitBeforeGrouping,
            'parts'                => $parts,
            'returnNormalizedUrls' => $returnNormalizedUrls,
        ),
        $optional
    );
  }

  /**
   * Get translations for the transitions
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getTransitionsTranslations($optional = array())
  {
    return $this->_request(
        'Transitions.getTranslations',
        array(),
        $optional
    );
  }

  /**
   * MODULE: USER COUNTRY
   * Get visitors country information
   */

  /**
   * Get countries of all visitors
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCountry($segment = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getCountry',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get continents of all visitors
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getContinent($segment = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getContinent',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get regions of all visitors
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getRegion($segment = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getRegion',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get cities of all visitors
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCity($segment = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getCity',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get location from ip
   *
   * @param string $ip
   * @param string $provider
   * @param array  $optional
   *
   * @return bool
   */
  public function getLocationFromIP($ip, $provider = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getLocationFromIP',
        array(
            'ip'       => $ip,
            'provider' => $provider,
        ),
        $optional
    );
  }

  /**
   * Get the number of disting countries
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getCountryNumber($segment = '', $optional = array())
  {
    return $this->_request(
        'UserCountry.getNumberOfDistinctCountries',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: USER Resultion
   * Get screen resolutions
   */

  /**
   * Get resolution
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getResolution($segment = '', $optional = array())
  {
    return $this->_request(
        'Resolution.getResolution',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get configuration
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getConfiguration($segment = '', $optional = array())
  {
    return $this->_request(
        'Resolution.getConfiguration',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: DEVICE PLUGINS
   * Get device plugins
   */

  /**
   * Get plugins
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserPlugin($segment = '', $optional = array())
  {
    return $this->_request(
        'DevicePlugins.getPlugin',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: USER LANGUAGE
   * Get the user language
   */

  /**
   * Get language
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserLanguage($segment = '', $optional = array())
  {
    return $this->_request(
        'UserLanguage.getLanguage',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get language code
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserLanguageCode($segment = '', $optional = array())
  {
    return $this->_request(
        'UserLanguage.getLanguageCode',
        array(
            'segment' => $segment,
        ),
        $optional
    );
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
   * @param array  $optional
   *
   * @return bool
   */
  public function setUserPreference($userLogin, $preferenceName, $preferenceValue, $optional = array())
  {
    return $this->_request(
        'UsersManager.setUserPreference',
        array(
            'userLogin'       => $userLogin,
            'preferenceName'  => $preferenceName,
            'preferenceValue' => $preferenceValue,
        ),
        $optional
    );
  }

  /**
   * Get user preference
   *
   * @param string $userLogin Username
   * @param string $preferenceName
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserPreference($userLogin, $preferenceName, $optional = array())
  {
    return $this->_request(
        'UsersManager.getUserPreference',
        array(
            'userLogin'      => $userLogin,
            'preferenceName' => $preferenceName,
        ),
        $optional
    );
  }

  /**
   * Get user by username
   *
   * @param array|string $userLogins Array with Usernames
   * @param array        $optional
   *
   * @return bool
   */
  public function getUsers($userLogins = '', $optional = array())
  {
    return $this->_request(
        'UsersManager.getUsers',
        array(
            'userLogins' => $userLogins,
        ),
        $optional
    );
  }

  /**
   * Get all user logins
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getUsersLogin($optional = array())
  {
    return $this->_request(
        'UsersManager.getUsersLogin',
        array(),
        $optional
    );
  }

  /**
   * Get sites by user access
   *
   * @param string $access
   * @param array  $optional
   *
   * @return bool
   */
  public function getUsersSitesFromAccess($access, $optional = array())
  {
    return $this->_request(
        'UsersManager.getUsersSitesFromAccess',
        array(
            'access' => $access,
        ),
        $optional
    );
  }

  /**
   * Get all users with access level from the current site
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getUsersAccess($optional = array())
  {
    return $this->_request(
        'UsersManager.getUsersAccessFromSite',
        array(),
        $optional
    );
  }

  /**
   * Get all users with access $access to the current site
   *
   * @param string $access
   * @param array  $optional
   *
   * @return bool
   */
  public function getUsersWithSiteAccess($access, $optional = array())
  {
    return $this->_request(
        'UsersManager.getUsersWithSiteAccess',
        array(
            'access' => $access,
        ),
        $optional
    );
  }

  /**
   * Get site access from the user $userLogin
   *
   * @param string $userLogin Username
   * @param array  $optional
   *
   * @return bool
   */
  public function getSitesAccessFromUser($userLogin, $optional = array())
  {
    return $this->_request(
        'UsersManager.getSitesAccessFromUser',
        array(
            'userLogin' => $userLogin,
        ),
        $optional
    );
  }

  /**
   * Get user by login
   *
   * @param string $userLogin Username
   * @param array  $optional
   *
   * @return bool
   */
  public function getUser($userLogin, $optional = array())
  {
    return $this->_request(
        'UsersManager.getUser',
        array(
            'userLogin' => $userLogin,
        ),
        $optional
    );
  }

  /**
   * Get user by email
   *
   * @param string $userEmail
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserByEmail($userEmail, $optional = array())
  {
    return $this->_request(
        'UsersManager.getUserByEmail',
        array(
            'userEmail' => $userEmail,
        ),
        $optional
    );
  }

  /**
   * Add a user
   *
   * @param string $userLogin Username
   * @param string $password  Password in clear text
   * @param string $email
   * @param string $alias
   * @param array  $optional
   *
   * @return bool
   */
  public function addUser($userLogin, $password, $email, $alias = '', $optional = array())
  {
    return $this->_request(
        'UsersManager.addUser',
        array(
            'userLogin' => $userLogin,
            'password'  => $password,
            'email'     => $email,
            'alias'     => $alias,
        ),
        $optional
    );
  }

  /**
   * Set super user access
   *
   * @param string $userLogin Username
   * @param int    $hasSuperUserAccess
   * @param array  $optional
   *
   * @return bool
   */
  public function setSuperUserAccess($userLogin, $hasSuperUserAccess, $optional = array())
  {
    return $this->_request(
        'UsersManager.setSuperUserAccess',
        array(
            'userLogin'          => $userLogin,
            'hasSuperUserAccess' => $hasSuperUserAccess,
        ),
        $optional
    );
  }

  /**
   * Check if user has super user access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function hasSuperUserAccess($optional = array())
  {
    return $this->_request(
        'UsersManager.hasSuperUserAccess',
        array(),
        $optional
    );
  }

  /**
   * Get a list of users with super user access
   *
   * @param array $optional
   *
   * @return bool
   */
  public function getUsersHavingSuperUserAccess($optional = array())
  {
    return $this->_request(
        'UsersManager.getUsersHavingSuperUserAccess',
        array(),
        $optional
    );
  }

  /**
   * Update a user
   *
   * @param string $userLogin Username
   * @param string $password  Password in clear text
   * @param string $email
   * @param string $alias
   * @param array  $optional
   *
   * @return bool
   */
  public function updateUser($userLogin, $password = '', $email = '', $alias = '', $optional = array())
  {
    return $this->_request(
        'UsersManager.updateUser',
        array(
            'userLogin' => $userLogin,
            'password'  => $password,
            'email'     => $email,
            'alias'     => $alias,
        ),
        $optional
    );
  }

  /**
   * Delete a user
   *
   * @param string $userLogin Username
   * @param array  $optional
   *
   * @return bool
   */
  public function deleteUser($userLogin, $optional = array())
  {
    return $this->_request(
        'UsersManager.deleteUser',
        array(
            'userLogin' => $userLogin,
        ),
        $optional
    );
  }

  /**
   * Checks if a user exist
   *
   * @param string $userLogin
   * @param array  $optional
   *
   * @return bool
   */
  public function userExists($userLogin, $optional = array())
  {
    return $this->_request(
        'UsersManager.userExists',
        array(
            'userLogin' => $userLogin,
        ),
        $optional
    );
  }

  /**
   * Checks if a user exist by email
   *
   * @param string $userEmail
   * @param array  $optional
   *
   * @return bool
   */
  public function userEmailExists($userEmail, $optional = array())
  {
    return $this->_request(
        'UsersManager.userEmailExists',
        array(
            'userEmail' => $userEmail,
        ),
        $optional
    );
  }

  /**
   * Grant access to multiple sites
   *
   * @param string $userLogin Username
   * @param string $access
   * @param array  $idSites
   * @param array  $optional
   *
   * @return bool
   */
  public function setUserAccess($userLogin, $access, $idSites, $optional = array())
  {
    return $this->_request(
        'UsersManager.setUserAccess',
        array(
            'userLogin' => $userLogin,
            'access'    => $access,
            'idSites'   => $idSites,
        ),
        $optional
    );
  }

  /**
   * Get the token for a user
   *
   * @param string $userLogin   Username
   * @param string $md5Password Password in clear text
   * @param array  $optional
   *
   * @return bool
   */
  public function getTokenAuth($userLogin, $md5Password, $optional = array())
  {
    return $this->_request(
        'UsersManager.getTokenAuth',
        array(
            'userLogin'   => $userLogin,
            'md5Password' => md5($md5Password),
        ),
        $optional
    );
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
   * @param array  $optional
   *
   * @return bool
   */
  public function getVisitFrequency($segment = '', $columns = '', $optional = array())
  {
    return $this->_request(
        'VisitFrequency.get',
        array(
            'segment' => $segment,
            'columns' => $columns,
        ),
        $optional
    );
  }

  /**
   * MODULE: VISIT TIME
   * Get visit time
   */

  /**
   * Get the visit by local time
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getVisitLocalTime($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitTime.getVisitInformationPerLocalTime',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the visit by server time
   *
   * @param string         $segment
   * @param boolean|string $hideFutureHoursWhenToday Hide the future hours when the report is created for today
   * @param array          $optional
   *
   * @return bool
   */
  public function getVisitServerTime($segment = '', $hideFutureHoursWhenToday = '', $optional = array())
  {
    return $this->_request(
        'VisitTime.getVisitInformationPerServerTime',
        array(
            'segment'                  => $segment,
            'hideFutureHoursWhenToday' => $hideFutureHoursWhenToday,
        ),
        $optional
    );
  }

  /**
   * Get the visit by server time
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getByDayOfWeek($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitTime.getByDayOfWeek',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * MODULE: VISITOR INTEREST
   * Get the interests of the visitor
   */

  /**
   * Get the number of visits per visit duration
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfVisitsPerDuration($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitorInterest.getNumberOfVisitsPerVisitDuration',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of visits per visited page
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfVisitsPerPage($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitorInterest.getNumberOfVisitsPerPage',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of days elapsed since the last visit
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfVisitsByDaySinceLast($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitorInterest.getNumberOfVisitsByDaysSinceLast',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the number of visits by visit count
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getNumberOfVisitsByCount($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitorInterest.getNumberOfVisitsByVisitCount',
        array(
            'segment' => $segment,
        ),
        $optional
    );
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
   * @param array  $optional
   *
   * @return bool
   */
  public function getVisitsSummary($segment = '', $columns = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.get',
        array(
            'segment' => $segment,
            'columns' => $columns,
        ),
        $optional
    );
  }

  /**
   * Get visits
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getVisits($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getVisits',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get unique visits
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUniqueVisitors($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getUniqueVisitors',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get user visit summary
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getUserVisitors($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getUsers',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get actions
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getActions($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getActions',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get max actions
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getMaxActions($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getMaxActions',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get bounce count
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getBounceCount($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getBounceCount',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get converted visits
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getVisitsConverted($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getVisitsConverted',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the sum of all visit lengths
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSumVisitsLength($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getSumVisitsLength',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }

  /**
   * Get the sum of all visit lengths formated in the current language
   *
   * @param string $segment
   * @param array  $optional
   *
   * @return bool
   */
  public function getSumVisitsLengthPretty($segment = '', $optional = array())
  {
    return $this->_request(
        'VisitsSummary.getSumVisitsLengthPretty',
        array(
            'segment' => $segment,
        ),
        $optional
    );
  }
}
