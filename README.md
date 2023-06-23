# Matomo PHP API

[![PHPUnit](https://github.com/VisualAppeal/Matomo-PHP-API/actions/workflows/tests.yml/badge.svg)](https://github.com/VisualAppeal/Matomo-PHP-API/actions/workflows/tests.yml) [![Packagist](https://img.shields.io/packagist/dt/visualappeal/matomo-php-api)](https://packagist.org/packages/visualappeal/matomo-php-api)

A PHP wrapper class for the [Matomo](https://matomo.org/) API.

## Requirements

* PHP >= 8.0 (for php 7.3/7.4 use version 1.6.1)
* cUrl (php-curl)
* JSON (php-json)

## Install

This library can be installed via composer: `composer require visualappeal/matomo-php-api`

## Usage

### Create an instance of matomo

```php
require(__DIR__ . '/vendor/autoload.php');

use VisualAppeal\Matomo;

$matomo = new Matomo('http://stats.example.org', 'my_access_token', 'siteId');
```

There are some basic parameters (period, date, range) which you can define at the beginning. They do not change until you reset them with

```php
$matomo->reset();
```

So you can execute multiple requests without specifying the parameters again.

### siteId

The ID of your website, single number, list separated through comma `"1,4,7"`, or `"all"`.

### period

The period you request the statistics for

```php
Matomo::PERIOD_DAY
Matomo::PERIOD_WEEK
Matomo::PERIOD_MONTH
Matomo::PERIOD_YEAR
Matomo::PERIOD_RANGE
```

If you set the period to `Matomo::PERIOD_RANGE` you can specify the range via

```php
$matomo->setRange('2012-01-14', '2012-04-30'); //All data from the first to the last date
$matomo->setRange('2012-01-14', Matomo::DATE_YESTERDAY); //All data from the first date until yesterday
$matomo->setRange('2012-01-14'); //All data from the first date until now
```

If you set it to something other than `Matomo::PERIOD_RANGE` you can specify the date via:

```php
$matomo->setPeriod(x);
$matomo->setDate('2012-03-03');

Case x of PERIOD_DAY the report is created for the third of march, 2012
Case x of PERIOD_WEEK the report is created for the first week of march, 2012
Case x of PERIOD_MONTH the report is created for march, 2012
Case x of PERIOD_YEAR the report is created for 2012
```

### date

Set the date via

```php
$matomo->setDate('YYYY-mm-dd');
```

Or use the constants

```php
$matomo->setDate(Matomo::DATE_TODAY);
$matomo->setDate(Matomo::DATE_YESTERDAY);
```

Report for the last seven weeks including the current week

```php
$matomo->setPeriod(Matomo::PERIOD_WEEK);
$matomo->setDate('last7');
```

Report for the last 2 years without the current year

```
$matomo->setPeriod(Matomo::PERIOD_YEAR);
$matomo->setDate('previous2');
```

### segment, idSubtable, expanded

For some functions you can specify `segment`, `idSubtable` and `expanded`. Please refer to the matomo [segment documentation](https://developer.matomo.org/api-reference/reporting-api-segmentation) and to the [api reference](https://developer.matomo.org/api-reference/reporting-api) for more information about these parameters.

### format

Specify a output format via

```php
$matomo->setFormat(Matomo::FORMAT_JSON);
```

JSON is converted with `json_decode` before returning the request.

All available formats

```php
Matomo::FORMAT_XML
Matomo::FORMAT_JSON
Matomo::FORMAT_CSV
Matomo::FORMAT_TSV
Matomo::FORMAT_HTML
Matomo::FORMAT_RSS
Matomo::FORMAT_PHP
```


## Example

Get all the unique visitors from yesterday:

```php
require(__DIR__ . '/vendor/autoload.php');

use VisualAppeal\Matomo;

$matomo = new Matomo('http://stats.example.org', 'my_access_token', 1, Matomo::FORMAT_JSON);

$matomo->setPeriod(Matomo::PERIOD_DAY);
$matomo->setDate(Matomo::DATE_YESTERDAY);

echo 'Unique visitors yesterday: ' . $matomo->getUniqueVisitors();
```
