## Readme

A PHP wrapper class for [Piwik](http://piwik.org/)

## Requirements

* cUrl

## Use

### Create an instance of piwik

	require('Piwik.php');
	$piwik = new Piwik('http://stats.example.org', 'my_access_token', 'siteId');

There are some basic parameters (period, date, range) which you can define at the beginning. They do not change until you reset them with

	$piwik->reset();

So you can execute multiple requests without specifying the parameters again.

### siteId 

The ID of your website, single number, list separated through comma "1,4,7", or "all"

### period

The period you request the statistics for 

	Piwik::PERIOD_DAY
	Piwik::PERIOD_WEEK
	Piwik::PERIOD_MONTH
	Piwik::PERIOD_YEAR
	Piwik::PERIOD_RANGE

If you set the period to `Piwik::PERIOD_RANGE` you can specify the range via

	$piwik->setRange('2012-01-14', '2012-04-30'); //All data from the first to the last date
	$piwik->setRange('2012-01-14', Piwik::DATE_YESTERDAY); //All data from the first until yesterday
	$piwik->setRange('2012-01-14'); //All data from the first until now

__When you use the period range you do not need to specify a date!__

If you set it to something other than `Piwik::PERIOD_RANGE` you can specify the date via

	$piwik->setPeriod(x);
	$piwik->setDate('2012-03-03');

	Case x of PERIOD_DAY the report is created for the third of march, 2012
	Case x of PERIOD_WEEK the report is created for the first week of march, 2012
	Case x of PERIOD_MONTH the report is created for march, 2012
	Case x of PERIOD_YEAR the report is created for 2012

### date

Set the date via

	$piwik->setDate('YYYY-mm-dd');

Or use the constants
	
	$piwik->setDate(Piwik::DATE_TODAY);
	$piwik->setDate(Piwik::DATE_YESTERDAY);

Report for the last seven weeks including the current week

	$piwik->setPeriod(Piwik::PERIOD_WEEK);
	$piwik->setDate('last7');

Report for the last 2 years without the current year

	$piwik->setPeriod(Piwik::PERIOD_YEAR);
	$piwik->setDate('previous2');

### segment, idSubtable, expanded

For some functions you can specify `segment`, `idSubtable` and `expanded`. Please refer to the piwik [segment documentation](http://piwik.org/docs/analytics-api/segmentation/) and to the [api reference](http://piwik.org/docs/analytics-api/reference/) for more information about these parameters.

### format

Specify a output format via

	$piwik->setFormat(Piwik::FORMAT_JSON);

JSON is converted with `json_decode` before returning the request.

All available formats

	Piwik::FORMAT_XML
	Piwik::FORMAT_JSON
	Piwik::FORMAT_CSV
	Piwik::FORMAT_TSV
	Piwik::FORMAT_HTML
	Piwik::FORMAT_RSS
	Piwik::FORMAT_PHP


## Example

Get all the unique visitors from yesterday:

	require('piwik.php');
	$piwik = new Piwik('http://stats.example.org', 'my_access_token', 1, Piwik::FORMAT_JSON);
	
	$piwik->setPeriod(Piwik::PERIOD_DAY);
	$piwik->setDate(Piwik::DATE_YESTERDAY);
	
	echo "Unique visitors yesterday: ".$piwik->getUniqueVisitors();

## License

Copyright 2012 - VisualAppeal GbR - www.visualappeal.de

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

`http://www.apache.org/licenses/LICENSE-2.0`

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
