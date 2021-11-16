<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Matomo PHP API</title>
	</head>
	<body>
<?php

require(__DIR__ . '/vendor/autoload.php');
require('config.php');

use VisualAppeal\Matomo;

$matomo = new Matomo(SITE_URL, TOKEN, SITE_ID, Matomo::FORMAT_JSON);
$matomo->setLanguage('en');

// $matomo->verifySsl = false;

try {
	$test = $matomo->getApi();

	//Default time period: yesterday
	$visits = $matomo->getVisits();
	$visitsU = $matomo->getUniqueVisitors();
	$visitsL = $matomo->getSumVisitsLengthPretty();

	//Change time period to current year

	$matomo->setPeriod(Matomo::PERIOD_YEAR);
	$matomo->setDate(date('Y-m-d'));

	$visitsYear = $matomo->getVisits();

	//Change time period to range
	$matomo->setPeriod(Matomo::PERIOD_RANGE);
	$matomo->setRange(date('Y-m-d', mktime(0, 0, 0, 11, 24, 2014)), date('Y-m-d', mktime(0, 0, 0, 11, 31, 2014)));

	?>

	<h2>Summary <?php echo date('Y') ?></h2>
	<ul>
		<li>Visit count: <?php echo $visitsYear; ?></li>
	</ul>


<?php
} catch (Exception $e) {
	echo '<pre>';
	var_dump($e);
	echo '</pre>';
}
?>
	</body>
</html>
