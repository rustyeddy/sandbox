<?php

$settings = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        // Allow the web server to send the content-length header
        'addContentLengthHeader' => false, 

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => '/srv/logs/goeslim.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // MongoDB settings
        'db' => [
            'dbname'    => 'goes',
            'username'  => 'goes',
            'password'  => 'password',
        ],

        'data' => [
            'rawdata'   => '/srv/goesdata/datablocks',
            'archive'   => '/srv/goesdata/archive',
            'failed'    => '/srv/goesdata/failed',
        ],
    ],
];

// Merge / Override private data with global / default settings
$s2 = [];
$cfgfile = '/etc/goes.php';
if (file_exists($cfgfile)) {
    $s = require($cfgfile);
    $settings['settings'] = array_merge($settings['settings'], $s['settings']);
}
return $settings;