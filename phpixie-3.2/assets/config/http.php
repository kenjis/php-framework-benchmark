<?php

return array(
    'resolver' => array(
        'type' => 'pattern',
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
