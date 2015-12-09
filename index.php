<?php

$stack = getenv('stack') ? getenv('stack') : 'local';
$output_dir = __DIR__ . '/output/' . $stack;
$host = null;
switch ($stack) {
    case 'local':
        $host = 'localhost';
        break;
    default:
        $host = str_replace('docker_', '', $stack);
        break;
};

Parse_Results: {
    require __DIR__ . '/libs/parse_results.php';
    $results = parse_results($output_dir . '/results.hello_world.log');
}

Load_Theme: {
    $theme = isset($_GET['theme']) ? $_GET['theme'] : 'default';
    if (! ctype_alnum($theme)) {
        exit('Invalid theme');
    }

    if ($theme === 'default') {
        require __DIR__ . '/libs/make_graph.php';
    } else {
        $file = __DIR__ . '/libs/' . $theme . '/make_graph.php';
        if (is_readable($file)) {
            require $file;
        } else {
            require __DIR__ . '/libs/make_graph.php';
        }
    }
}

$max_rps = 2300;
$max_memory = 3;
$max_time = 500;
$max_file = 300;

// RPS Benchmark
list($chart_rpm, $div_rpm) = make_graph('rps', 'Throughput', 'requests per second', $max_rps);

// Memory Benchmark
list($chart_mem, $div_mem) = make_graph('memory', 'Memory', 'peak memory (MB)', $max_memory);

// Exec Time Benchmark
list($chart_time, $div_time) = make_graph('time', 'Exec Time', 'ms', $max_time);

// Included Files
list($chart_file, $div_file) = make_graph('file', 'Included Files', 'count', $max_file);
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
<h3><?php echo $stack; ?></h3>
<div>
<?php
echo $div_rpm, $div_mem, $div_time, $div_file;
?>
</div>

<ul>
<?php
$url_file = $output_dir . '/urls.log';
if (file_exists($url_file)) {
    $urls = file($url_file);
    foreach ($urls as $url) {
        $url = str_replace(['127.0.0.1', $host], $_SERVER['HTTP_HOST'], $url);
        echo '<li><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') .
             '">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') .
             '</a></li>' . "\n";
    }
}
?>
</ul>

<hr>

<h3>Error log</h3>
<pre><?php echo file_get_contents($output_dir . '/error.hello_world.log'); ?></pre>

<hr>

<footer>
    <p style="text-align: right">This page is a part of <a href="https://github.com/kenjis/php-framework-benchmark">php-framework-benchmark</a>.</p>
</footer>
</body>
</html>
