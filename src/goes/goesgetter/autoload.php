<?php

require dirname(__FILE__) . '/vendor/autoload.php';

use SierraHydrog\Goes\Commands as Commands;

/**
 * SierraHydrog\Goes autoloader
 */
spl_autoload_register(function($class) {

    // Project specific namespace
    $prefix = 'SierraHydrog\\Goes\\';

    // Base directory for the namespace
    $basedir = __DIR__ . '/src/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, not found in this autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base dir
    $file = $basedir . str_replace('\\', '/', $relative_class) . ".php";

    // if file exists require it
    if (file_exists($file)) {
        require $file;
    }
});
