<?php

/**
 * Routes to the AQUARIUS API
 */
// Get all sites.
$app->get('/aquarius/locations', function($request, $response, $args) {
    $aq = $this->aquarius;
    $locs = $sm->getLocations();

    $newresp = $response->withJson($locs);
    return $newresp;
});
