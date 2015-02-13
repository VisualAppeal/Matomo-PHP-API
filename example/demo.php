<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Piwik PHP API</title>
	</head>
	<body>
<?php

require(__DIR__ . '/vendor/autoload.php');
require('config.php');

use VisualAppeal\Piwik;

$piwik = new Piwik(SITE_URL, TOKEN, SITE_ID, Piwik::FORMAT_JSON);
$piwik->setLanguage('en');

// $piwik->verifySsl = false;

$test = $piwik->getApi();

if ($piwik->hasError()) {
	echo '<p>Invalid request</p>';
	echo '<pre>';
	var_dump($piwik->getErrors());
	echo '</pre>';
} else {
	//Default time period: yesterday
	$visits = $piwik->getVisits();
	$visitsU = $piwik->getUniqueVisitors();
	$visitsL = $piwik->getSumVisitsLengthPretty();

	//Change time period to current year

	$piwik->setPeriod(Piwik::PERIOD_YEAR);
	$piwik->setDate(date('Y-m-d'));

	$visitsYear = $piwik->getVisits();
	$visitsUYear = $piwik->getUniqueVisitors(); // To enable see http://piwik.org/faq/how-to/faq_113/
	$visitsLYear = $piwik->getSumVisitsLengthPretty();

	//Change time period to range
	$piwik->setPeriod(Piwik::PERIOD_RANGE);
	$piwik->setRange(date('Y-m-d', mktime(0, 0, 0, 11, 24, 2014)), date('Y-m-d', mktime(0, 0, 0, 11, 31, 2014)));

	$visitsRange = $piwik->getVisits();
	$visitsURange = $piwik->getUniqueVisitors(); // To enable see http://piwik.org/faq/how-to/faq_113/
	$visitsLRange = $piwik->getSumVisitsLengthPretty();
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

	<?php if ($piwik->hasError()): ?>
		<h2>Error Summary</h2>
		<ul>
			<?php foreach ($piwik->getErrors() as $error): ?>
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
