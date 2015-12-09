<?php
 return array (
  'web' => 
  array (
    '*' => 
    array (
      'home-subdirectory' =>
      array (
        'name' => 'home-subdirectory',
        'regexp' => 'php-framework-benchmark/nofuss-1.2/html/index.php/hello/index',
        'controller' => 'home/index/index',
        'inheritableRegexp' => 'nofuss-1.2/html/index.php/hello/index',
      ),
      'home' =>
      array (
        'name' => 'home',
        'regexp' => 'nofuss-1.2/html/index.php/hello/index',
        'controller' => 'home/index/index',
        'inheritableRegexp' => 'nofuss-1.2/html/index.php/hello/index',
      ),
    ),
  ),
);