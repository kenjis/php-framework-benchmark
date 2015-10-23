<?php

return array(
    'translator' => array(
        'basePath' => '/php-framework-benchmark/phpixie-3.2/web/index.php/'
    ),
    'resolver' => array(
        'type' => 'pattern',
        'path' => 'hello',
        'defaults' => array(
            'bundle' => 'app'
        )
    ),
    'exceptionResponse' => array(
        'template' => 'framework:http/exception'
    ),
    'notFoundResponse' => array(
        'template' => 'framework:http/notFound'
    )
);
