## Changelog

### 1.5.2 (2019/08/01)

* Fixed: The site ID can now be of mixed type again (e.g. 5, "9", "3,24,27", "all") or null

### 1.5.1 (2019/07/22)

* Changed: Moved changelog to separate file
* Changed: Updated dependencies

### 1.5.0 (2019/04/11)

* Changed: PHP >= 7.2 is now required
* Changed: Added type hints
* Fixed: Filter Limit

### 1.4.1 (2018/10/05)

* Added: Compatible to Matomo 3.6.1

### 1.4.0 (2018/10/05)

* Renamed to Matomo-PHP-API
* The `Piwik` class is now called `Matomo`
* Changed license from Apache 2.0 to MIT

### 1.2.2 (2016/09/17)

* Changed: Security fix

### 1.2.1 (2015/11/09)

* Added: Compatible to Piwik 2.15.1

### 1.2.0 (2015/05/03)

* Changed: Removed optional parameters for the methods and added optional parameters array. Some methods signatures changed, so please check your methods before upgrading.

For example `getUrlsForSocial($segment = '', $idSubtable = '')` is now `getUrlsForSocial($segment = '', $optional = [])`. So instead of calling `$matomo->getUrlsForSocial('browserCode==FF;country==DE', 4)` you have to call `$matomo->getUrlsForSocial('browserCode==FF;country==DE', ['idSubtable' => 4])`.

* Added: Compatible to Piwik 2.13.0

### 1.1.2 (2015/03/22)

* Fixed: Errors were not appended to error array
* Changed: Requires PHP 5.4 ([5.3 is not supported anymore](http://php.net/archive/2014.php#id2014-08-14-1))
* Added: Unit tests

### 1.1.1 (2015/02/18)

* Added: Get separate data entries for a date range without the range period parameter [#14](https://github.com/VisualAppeal/Matomo-PHP-API/issues/14)
* Added: Compatible to Piwik 2.11

### 1.1.0 (2015/02/13)

* Changed: Support for PSR-4 autoloading

### 1.0.1 (2015/02/13)

* Fixed: Multiple bugs

### 1.0.0 (2014/12/13)

* Added: Compatibility to piwik 2.10.0
