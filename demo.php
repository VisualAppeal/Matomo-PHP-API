<?php 

require('piwik.php');

//Edit
define('SITE_URL', 'http://stats.example.com/');
define('SITE_ID', 1);
define('TOKEN', '123456789abcdefghijklmnopqrstuvwxyz');

$piwik = new Piwik(SITE_URL, TOKEN, SITE_ID, Piwik::FORMAT_JSON);
$piwik->setLanguage('en');

//Default time period: yesterday
$visits = $piwik->getVisits();
$visitsU = $piwik->getUniqueVisitors();
$visitsL = $piwik->getSumVisitsLengthPretty();

//Change time period to current year

$piwik->setPeriod(Piwik::PERIOD_YEAR);
$piwik->setDate(date('Y-m-d'));

$visitsYear = $piwik->getVisits();
$visitsUYear = $piwik->getUniqueVisitors();
$visitsLYear = $piwik->getSumVisitsLengthPretty();

//Change time period to range
$piwik->setPeriod(Piwik::PERIOD_RANGE);
$piwik->setRange(date('Y-m-d', mktime(0, 0, 0, 12, 24, 2011)), date('Y-m-d', mktime(0, 0, 0, 12, 31, 2011)));

$visitsRange = $piwik->getVisits();
$visitsURange = $piwik->getUniqueVisitors();
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
<p><strong>No error!</strong></p>
<?php endif; ?>