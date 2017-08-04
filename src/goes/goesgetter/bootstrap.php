<?php
/**
 * Rusty Eddy
 * User: Rusty
 * Date: 1/5/16
 * Time: 5:56 AM
 */

namespace SierraHydrog\Goes;

$basedir = dirname(__FILE__) . "/";
$autoloadFile = $basedir . 'autoload.php';
$configFile   = $basedir . 'config.php';

global $argv;
global $commands;
global $config;

$progname = $argv[0];

// Determine if we are testing.
$weAreTesting = (\strpos($progname, "phpunit")) ? true : false;

// Modify autoload and config if we are testing.
if ($weAreTesting) {
    $testdir = $basedir . '/tests/';
    $configFile = $testdir . 'config.php';
}

if (!file_exists($autoloadFile)) {
    echo "Can not file " . $autoloadFile . " please fix.\n";
    exit(2);
}

require $autoloadFile;

if (!file_exists($configFile)) {
    echo "Please create a " . $configFile . " file before using this utility.\n";
    exit(2);
}
require $configFile;

// Determine if we have an output item in our config if not add one.
if (!array_key_exists('output', $config)) {
    $output = 'text';
    if ($weAreTesting) {
        $output = 'json';
    }

    // make HTML an option.
    $config['output'] = $output;
}

// Create the commands.
$commands = Commands\Commands::getInstance();

/*
 * Get a logger but turn off by default.
 */
\Logger::configure($basedir . 'etc/logger.xml');
$log = \Logger::getRootLogger( "GOES" );

/**
 * Make sure we have all the directories created that we expect.
 */
foreach (['newfiledir', 'archivedir', 'stored', 'faildir' ] as $dir) {
    $path = $config[$dir];
    if (!file_exists($path)) {
        $ok = mkdir($path, 0755, true);
        output("trying to create " . $path . " " . $ok . "\n");
    }
}

function output($str) {
    global $config;
    if (array_key_exists('verbose', $config) && $config['verbose'] > 0) {
        echo $str;
    }
}

