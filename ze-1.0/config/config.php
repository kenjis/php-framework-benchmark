<?php

use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

/**
 * Configuration files are loaded in a specific order. First ``global.php`` and afterwards ``local.php``. This way
 * local settings overwrite global settings.
 *
 * The configuration can be cached. This can be done by setting ``config_cache_enabled`` to ``true``.
 *
 * The configuration is stored in json so it is not depended on 3rd party libraries. Feel free to use something else
 * like Zend\Config\Writer to write PHP arrays.
 *
 * Obviously, if you use closures in your config you can't cache it.
 */

$cachedConfigFile = 'data/cache/app_config.php';

$config = [];
if (is_file($cachedConfigFile)) {
    // Try to load the cached config
    $config = json_decode(file_get_contents($cachedConfigFile), true);
} else {
    // Load configuration from autoload path
    foreach (Glob::glob('config/autoload/{{,*.}global,{,*.}local}.php', Glob::GLOB_BRACE) as $file) {
        $config = ArrayUtils::merge($config, include $file);
    }

    // Cache config if enabled
    if (isset($config['config_cache_enabled']) && $config['config_cache_enabled'] === true) {
        file_put_contents($cachedConfigFile, json_encode($config));
    }
}

// Return an ArrayObject so we can inject the config as a service in Aura.Di
// and still use array checks like ``is_array``.
return new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS);
