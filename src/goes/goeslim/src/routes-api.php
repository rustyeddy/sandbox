<?php

/*
 * Data makes the following transition through life
 * 
 * 1. LRGS -> Blocks: comes out in one large file with multiple
 *      stations, times and measurements separated by '===='.
 *
 * 2. Blocks -> Raw Measurements: Parse out blocks and group each 
 *      measurements according to station + time (hourly) with all
 *      sensors. 
 * 
 * 3. Raw Measurements -> Normalized Form: Station and Sensors have
 *      been identified and values are all associated with
 *      Loc+time+sensor = value.   (Time series).
 *
 * 4. Timeseries -> Stored:  Timeseries are stored in AQUARIUS and
 *      optionally saved elsewhere.
 */

// Get all sites.
$app->get('/api/v1/sites', function($request, $response, $args) {
    $sm = $this->siteManager;
    $sites = $sm->getSites();

    $newresp = $response->withJson($sites);
    return $newresp;
});

/*
 * This will get large raw chunks of data from LRGS and leave it
 * in the file system as a whole chunk.
 *
 * XXX = Broken
 */
$app->get('/api/v1/rawdata', function($request, $response, $args) {

    $streamer = $this->dataFactory;

    /*
     * This function will get the data stream in chunks and save it 
     * to a redis cache.  We won't see the data here, however we will
     * be told how many chunks have been stored in Redis.
     */
    $blocks = $streamer->getStreamData('midhde', 'cilz-ZKOK-24');
    $resp = [ 'blocks-recieved' => $blocks ];
    $nresp = $response->withJson($resp);
    return $nresp;
});

$app->get('/api/v1/bgf', function($request, $response, $args) {
    $df = $this->dataFactory;
    $resp = $df->testBGF();
    $nresp = $response->withJson($resp);
    return $resp;
});

/**
 * Break apart rawdata files and cache unparsed measurements.
 */
$app->get('/api/v1/measurements', function($request, $response, $args) {
    $streamer = $this->dataFactory;
    $processed = $streamer->processBlockFiles();
    if ($processed == false) {
        $processed = "No files process call error";
    }
    $resp = ['files-processed' => $processed];
    $nresp = $response->withJson($resp);
    return $nresp;
});

/**
 * Get cached measurements and parse in to site specific timeseries
 * data.
 */ 
$app->get('/api/v1/timeseries', function($request, $response, $args) {
    $ds = $this->dataFactory;
    $res = $ds->processMeasurements();
    $nresp = $response->withJson($res);
    return $nresp;
});

/**
 * Store timeseries data in aquarius and possibly a DB/AWS?
 */
$app->get('/api/v1/aquarius', function($request, $response, $args) {

    $count = array_key_exists('count', $args) ?
           args['count'] : 0;

    $ds = $this->dataFactory;
    $resp = $ds->storeMeasurements($count);
    $nresp = $response->withJson($resp);
    return $nresp;

});
