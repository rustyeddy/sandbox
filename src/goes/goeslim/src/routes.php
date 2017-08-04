<?php

$app->get('/', function ($request, $response, $args) {
    // Give a list of all routes?
    $this->logger->info("Slim-Skater /");

    $args['files'] = $this->dataFactory->countBlockFiles();
    $args['raw'] = $this->cache->llen('goesraw');
    $args['meas'] = $this->cache->llen('measurements');
    $args['failed'] = $this->cache->llen('measurements-failed');
    $args['written'] = $this->cache->get('aquarius-saved');

    $sm = $this->siteManager;
    $args['sites'] = $sm->getSites();

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
