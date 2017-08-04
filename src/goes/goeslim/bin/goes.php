#!/usr/bin/php
<?php

// Change to root directory and autoload files
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

// Convert command line arguments into a URL
$argv = $GLOBALS['argv'];
array_shift($GLOBALS['argv']);
$pathinfo = implode('/', $argv);

// Create our app instance
// Set up the env so that Slim can route
$env = \Slim\Http\Environment::mock(['REQUEST_URI' => $pathinfo]);
$settings = require 'src/settings.php';
$settings['environment'] = $env;
$app = new \Slim\App($settings);

require 'src/dependencies.php';
require 'src/middleware.php';
require 'src/routes-cli.php';

/*
$container['notFoundHandler'] = function($c) {
    return function($request, $response, $exception) use ($c) {
        $body = $response->getBody();
        $body->write("not found");
        return $response->withBody($body);
    };
};

$container['errorHandler'] = function($c) {
    return function($request, $response, $exception) use ($c) {
        $body = $response->getBody();
        $body->write("command not found");
        return $response->withBody($body);
    };
};
*/


// $app->error(function (\Exception $e) use ($app) {
//     echo $e;
//     $app->stop();
// });

$app->run();
