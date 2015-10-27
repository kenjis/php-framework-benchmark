<?php

require __DIR__ . '/libs/php-recipe-2nd/make_chart_parts.php';
require __DIR__ . '/libs/parse_results.php';

$results = parse_results(__DIR__ . '/output/results.hello_world.log');

function makeGraph($id, $title, $hAxis_title)
{
    global $results;

    $barColors = array(
        'DarkBlue', 'DarkCyan', 'DarkGoldenRod', 'DarkGray', 'DarkGreen',
        'DarkKhaki', 'DarkMagenta', 'DarkOliveGreen', 'DarkOrange', 'DarkOrchid',
        'DarkRed', 'DarkSalmon', 'DarkSeaGreen', 'DarkSlateBlue', 'DarkSlateGray',
        'DarkBlue', 'DarkCyan', 'DarkGoldenRod', 'DarkGray', 'DarkGreen',
        'DarkKhaki', 'DarkMagenta', 'DarkOliveGreen', 'DarkOrange', 'DarkOrchid',
        'DarkRed', 'DarkSalmon', 'DarkSeaGreen', 'DarkSlateBlue', 'DarkSlateGray',
    );
    $graphWidth  = 1000;
    $graphHeight = 400;

    $data = array();
    $data[] = array('', $id, array('role' => 'style'));  // header

    $colors = $barColors;
    foreach ($results as $fw => $result) {
        $data[] = array($fw, $result[$id], array_shift($colors));
    }
    //var_dump($data); exit;

    $options = array(
      'title'  => $title,
      'titleTextStyle' => array('fontSize' => 16),
      'hAxis'  => array('title' => $hAxis_title,
                        'titleTextStyle' => array('bold' => true)),
      'vAxis'  => array('minValue' => 0, 'maxValue' => 0.01),
      'width'  => $graphWidth,
      'height' => $graphHeight,
      'bar'    => array('groupWidth' => '90%'),
      'legend' => array('position' => 'none')
    );
    $type = 'ColumnChart';
    return makeChartParts($data, $options, $type);
}

// RPS Benchmark
list($chart_rpm, $div_rpm) = makeGraph('rps', 'Throughput', 'requests per second');

// Memory Benchmark
list($chart_mem, $div_mem) = makeGraph('memory', 'Memory', 'peak memory (MB)');

// Exec Time Benchmark
list($chart_time, $div_time) = makeGraph('time', 'Exec Time', 'ms');

// Included Files
list($chart_file, $div_file) = makeGraph('file', 'Included Files', 'count');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PHP Framework Benchmark</title>
<script src="https://www.google.com/jsapi"></script>
<script>
<?php
echo $chart_rpm, $chart_mem, $chart_time, $chart_file;
?>
</script>
</head>
<body>
<h1>PHP Framework Benchmark</h1>
<h2>Hello World Benchmark</h2>
<div>
<?php
echo $div_rpm, $div_mem, $div_time, $div_file;
?>
</div>

<ul>
<?php
$url_file = __DIR__ . '/output/urls.log';
if (file_exists($url_file)) {
    $urls = file($url_file);
    foreach ($urls as $url) {
        $url = str_replace('127.0.0.1', $_SERVER['HTTP_HOST'], $url);
        echo '<li><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') .
             '">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') .
             '</a></li>' . "\n";
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
