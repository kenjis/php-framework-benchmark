<?php

require __DIR__ . '/libs/php-recipe-2nd/make_chart_parts.php';
require __DIR__ . '/libs/parse_results.php';

$results = parse_results(__DIR__ . '/output/results.hello_world.log');

$barColors = array(
    'DarkBlue', 'DarkCyan', 'DarkGoldenRod', 'DarkGray', 'DarkGreen',
    'DarkKhaki', 'DarkMagenta', 'DarkOliveGreen', 'DarkOrange', 'DarkOrchid',
    'DarkRed', 'DarkSalmon', 'DarkSeaGreen', 'DarkSlateBlue', 'DarkSlateGray',
);

$graphWidth  = 700;
$graphHeight = 400;

// RPS Benchmark
$data[] = array('', 'rps', array('role' => 'style'));  // header

$colors = $barColors;
foreach ($results as $fw => $result) {
    $data[] = array($fw, $result['rps'], array_shift($colors));
}
//var_dump($data); exit;

$options = array(
  'title'  => 'Throughput',
  'titleTextStyle' => array('fontSize' => 16),
  'hAxis'  => array('title' => 'requests per second',
                    'titleTextStyle' => array('bold' => true)),
  'vAxis'  => array('minValue' => 0, 'maxValue' => 0.01),
  'width'  => $graphWidth,
  'height' => $graphHeight,
  'bar'    => array('groupWidth' => '90%'),
  'legend' => array('position' => 'none')
);
$type = 'ColumnChart';
list($chart_rpm, $div_rpm) = makeChartParts($data, $options, $type);

// Memory Benchmark
$data = array();
$data[] = array('', 'memory', array('role' => 'style'));  // header

$colors = $barColors;
foreach ($results as $fw => $result) {
    $data[] = array($fw, $result['memory'], array_shift($colors));
}

$options = array(
  'title'  => 'Memory',
  'titleTextStyle' => array('fontSize' => 16),
  'hAxis'  => array('title' => 'peak memory (MB)',
                    'titleTextStyle' => array('bold' => true)),
  'vAxis'  => array('minValue' => 0, 'maxValue' => 1),
  'width'  => $graphWidth,
  'height' => $graphHeight,
  'bar'    => array('groupWidth' => '90%'),
  'legend' => array('position' => 'none')
);
$type = 'ColumnChart';
list($chart_mem, $div_mem) = makeChartParts($data, $options, $type);

// Exec Time Benchmark
$data = array();
$data[] = array('', 'time', array('role' => 'style'));  // header

$colors = $barColors;
foreach ($results as $fw => $result) {
    $data[] = array($fw, $result['time'], array_shift($colors));
}

$options = array(
  'title'  => 'Exec Time',
  'titleTextStyle' => array('fontSize' => 16),
  'hAxis'  => array('title' => 'ms',
                    'titleTextStyle' => array('bold' => true)),
  'vAxis'  => array('minValue' => 0, 'maxValue' => 1),
  'width'  => $graphWidth,
  'height' => $graphHeight,
  'bar'    => array('groupWidth' => '90%'),
  'legend' => array('position' => 'none')
);
$type = 'ColumnChart';
list($chart_time, $div_time) = makeChartParts($data, $options, $type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHP Framework Benchmark</title>
<script src="https://www.google.com/jsapi"></script>
<script>
<?php
echo $chart_rpm, $chart_mem, $chart_time;
?>
</script>
</head>
<body>
<h1>PHP Framework Benchmark</h1>
<h2>Hello World Benchmark</h2>
<div>
<?php
echo $div_rpm, $div_mem, $div_time;
?>
</div>

<ul>
<?php
$url_file = __DIR__ . '/output/urls.log';
if (file_exists($url_file)) {
    $urls = file($url_file);
    foreach ($urls as $url) {
        echo '<li><a href="' . $url . '">' . $url . '</a></li>' . "\n";
    }
}
?>
</ul>

<hr>

<footer>
    <p style="text-align: right">This page is a part of <a href="https://github.com/kenjis/php-framework-benchmark">php-framework-benchmark</a>.</p>
</footer>
</body>
</html>
