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

$test = $matomo->getApi();

if ($matomo->hasError()) {
	echo '<p>Invalid request</p>';
	echo '<pre>';
	var_dump($matomo->getErrors());
	echo '</pre>';
} else {
	//Default time period: yesterday
	$visits = $matomo->getVisits();
	$visitsU = $matomo->getUniqueVisitors();
	$visitsL = $matomo->getSumVisitsLengthPretty();

	//Change time period to current year

	$matomo->setPeriod(Matomo::PERIOD_YEAR);
	$matomo->setDate(date('Y-m-d'));

	$visitsYear = $matomo->getVisits();
	$visitsUYear = $matomo->getUniqueVisitors(); // To enable see https://matomo.org/faq/how-to/faq_113/
	$visitsLYear = $matomo->getSumVisitsLengthPretty();

	//Change time period to range
	$matomo->setPeriod(Matomo::PERIOD_RANGE);
	$matomo->setRange(date('Y-m-d', mktime(0, 0, 0, 11, 24, 2014)), date('Y-m-d', mktime(0, 0, 0, 11, 31, 2014)));

	$visitsRange = $matomo->getVisits();
	$visitsURange = $matomo->getUniqueVisitors(); // To enable see https://matomo.org/faq/how-to/faq_113/
	$visitsLRange = $matomo->getSumVisitsLengthPretty();
	?>

	<h2>Summary Yesterday</h2>
	<ul>
		<li>Visit count: <?php echo $visits; ?></li>
		<li>Unique visit count: <?php echo $visitsU; ?></li>
		<li>Summary of the visit lengths: <?php echo ($visitsL !== false) ? $visitsL : 0; ?></li>
	</ul>

	<h2>Summary <?php echo date('Y') ?></h2>
	<ul>
		<li>Visit count: <?php echo $visitsYear; ?></li>
		<li>Unique visit count: <?php echo $visitsUYear; ?></li>
		<li>Summary of the visit lengths: <?php echo ($visitsLYear !== false) ? $visitsLYear : 0; ?></li>
	</ul>

	<h2>Summary <?php echo date('Y-m-d', mktime(0, 0, 0, 12, 24, 2011)); ?> - <?php echo date('Y-m-d', mktime(0, 0, 0, 12, 31, 2011)); ?></h2>
	<ul>
		<li>Visit count: <?php echo $visitsRange; ?></li>
		<li>Unique visit count: <?php echo $visitsURange; ?></li>
		<li>Summary of the visit lengths: <?php echo ($visitsLRange !== false) ? $visitsLRange : 0; ?></li>
	</ul>

	<?php if ($matomo->hasError()): ?>
		<h2>Error Summary</h2>
		<ul>
			<?php foreach ($matomo->getErrors() as $error): ?>
				<li><?php echo $error; ?></li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<p><strong>No errors!</strong></p>
	<?php endif; ?>

<?php
}
?>
	</body>
</html>
