<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};


$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong<br/>' . $exception->getMessage());
    };
};

// Mongo
$container['db'] = function ($c) {
    $mongo = new MongoDB\Client('mongodb://localhost');
    $goes = $mongo->goes;
    return $goes;
};

// Redis
$container['cache'] = function ($c) {
    $red = new \Predis\Client('tcp://localhost');
    return $red;
};

// Site Manaager 
$container['siteManager'] = function($c) {
    // This should probable just be an instance
    $db = $c['db'];
    $sm = new \SierraHydrog\SiteManager();
    return $sm;
};

// The Data Factory
$container['dataFactory'] = function ($c) {
    $df = new \SierraHydrog\DataFactory();
    return $df;              
};

// Parser
$container['parser'] = function ($c) {
    $parser = new \SierraHydrog\Parser();
    return $parser;
};

// Time Series Manager
$container['timeSeriesManager'] = function ($c) {
    $tsm = new \SierraHydrog\TimeseriesManager();
    return $tsm;
};

$container['aquarius'] = function ($c) {
    $aq = new \SierraHydrog\Aquarius();
    return $aq;
};