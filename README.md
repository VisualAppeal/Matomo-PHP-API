# Matomo PHP API

[![Build Status](https://travis-ci.org/VisualAppeal/Matomo-PHP-API.svg)](https://travis-ci.org/VisualAppeal/Matomo-PHP-API)

A PHP wrapper class for the [Matomo](https://matomo.org/) API.

## Requirements

* PHP >= 5.4
* cUrl (php-curl)

## Install

This library can be installed via composer: `composer require visualappeal/matomo-php-api`

## Changelog

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

## Usage

### Create an instance of matomo

	require(__DIR__ . '/vendor/autoload.php');

	use VisualAppeal\Matomo;

	$matomo = new Matomo('http://stats.example.org', 'my_access_token', 'siteId');

There are some basic parameters (period, date, range) which you can define at the beginning. They do not change until you reset them with

	$matomo->reset();

So you can execute multiple requests without specifying the parameters again.

### siteId

The ID of your website, single number, list separated through comma "1,4,7", or "all"

### period

The period you request the statistics for

	Matomo::PERIOD_DAY
	Matomo::PERIOD_WEEK
	Matomo::PERIOD_MONTH
	Matomo::PERIOD_YEAR
	Matomo::PERIOD_RANGE

If you set the period to `Matomo::PERIOD_RANGE` you can specify the range via

	$matomo->setRange('2012-01-14', '2012-04-30'); //All data from the first to the last date
	$matomo->setRange('2012-01-14', Matomo::DATE_YESTERDAY); //All data from the first until yesterday
	$matomo->setRange('2012-01-14'); //All data from the first until now

__When you use the period range you do not need to specify a date!__

If you set it to something other than `Matomo::PERIOD_RANGE` you can specify the date via

	$matomo->setPeriod(x);
	$matomo->setDate('2012-03-03');

	Case x of PERIOD_DAY the report is created for the third of march, 2012
	Case x of PERIOD_WEEK the report is created for the first week of march, 2012
	Case x of PERIOD_MONTH the report is created for march, 2012
	Case x of PERIOD_YEAR the report is created for 2012

### date

Set the date via

	$matomo->setDate('YYYY-mm-dd');

Or use the constants

	$matomo->setDate(Matomo::DATE_TODAY);
	$matomo->setDate(Matomo::DATE_YESTERDAY);

Report for the last seven weeks including the current week

	$matomo->setPeriod(Matomo::PERIOD_WEEK);
	$matomo->setDate('last7');

Report for the last 2 years without the current year

	$matomo->setPeriod(Matomo::PERIOD_YEAR);
	$matomo->setDate('previous2');

### segment, idSubtable, expanded

For some functions you can specify `segment`, `idSubtable` and `expanded`. Please refer to the matomo [segment documentation](https://developer.matomo.org/api-reference/reporting-api-segmentation) and to the [api reference](https://developer.matomo.org/api-reference/reporting-api) for more information about these parameters.

### format

Specify a output format via

	$matomo->setFormat(Matomo::FORMAT_JSON);

JSON is converted with `json_decode` before returning the request.

All available formats

	Matomo::FORMAT_XML
	Matomo::FORMAT_JSON
	Matomo::FORMAT_CSV
	Matomo::FORMAT_TSV
	Matomo::FORMAT_HTML
	Matomo::FORMAT_RSS
	Matomo::FORMAT_PHP


## Example

Get all the unique visitors from yesterday:

	require(__DIR__ . '/vendor/autoload.php');

	use VisualAppeal\Matomo;

	$matomo = new Matomo('http://stats.example.org', 'my_access_token', 1, Matomo::FORMAT_JSON);

	$matomo->setPeriod(Matomo::PERIOD_DAY);
	$matomo->setDate(Matomo::DATE_YESTERDAY);

	echo 'Unique visitors yesterday: ' . $matomo->getUniqueVisitors();
