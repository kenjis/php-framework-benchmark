<?php

function frameworks()
{

    $frameworks = [
        //"no-framework",
        //"phalcon-1.3",
        "phalcon-2.0",
        "ice-1.0",
        "tipsy-0.10",
        "fatfree-3.5",
        "slim-2.6",
        "ci-3.0",
        "nofuss-1.2",
        "slim-3.0",
        "bear-1.0",
        "lumen-5.1",
        "ze-1.0",
        "radar-1.0-dev",
        "yii-2.0",
        //"lumen-5.0",
        //"silex-1.2",
        "silex-1.3",
        "cygnite-1.3",
        "fuel-1.8-dev",
        //"fuel-2.0-dev",
        "phpixie-3.2",
        //"cake-3.0",
        "aura-2.0",
        "cake-3.1",
        //"bear-0.10",
        //"symfony-2.5",
        //"symfony-2.6",
        "symfony-2.7",
        //"laravel-4.2",
        //"laravel-5.0",
        "laravel-5.1", // Currently segfaults a lot when running in docker stacks
        //"zf-2.4",
        "zf-2.5",
        //"typo3f-2.3",
        "typo3f-3.0", // Should probably be disabled since it attempts to connect to mysql
    ];

    if (strpos(getenv('stack'), 'hhvm') !== false) {
        return array_diff(
            $frameworks,
            array(
                'phalcon-1.3', // Not supported by HHVM
                'phalcon-2.0', // Not supported by HHVM
                'ice-1.0', // Not supported by HHVM
                'bear-1.0', // apc_fetch()-error on PHP7
                'laravel-5.1', // fails-with-no-such-file-or-directory
            )
        );
    }

    if (strpos(getenv('stack'), 'php_7') !== false) {
        return array_diff(
            $frameworks,
            array(
                'phalcon-1.3', // Not compiled for PHP7 - is it even supported?
                'phalcon-2.0', // Not compiled for PHP7 - is it even supported?
                'ice-1.0', // Not compiled for PHP7 - is it even supported?
                'bear-1.0', // apc_fetch()-error on PHP7
            )
        );
    }

    return $frameworks;

}
