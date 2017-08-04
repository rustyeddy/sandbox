#!/usr/bin/env php

<?php

require 'vendor/autoload.php';

function init_db()
{
    print "Checking if Mongo has been initialized\n";

    $mon = new MongoDB\Client('mongodb://localhost');
    $dcs = $mon->dcs;
    $cols = $dcs->listCollections();
    $ncols = iterator_count($cols);

    if ($ncols == 0) {
        print "No collections found\n";
        create_sites($dcs);
    } else {
        list_sites($dcs);
    }
}

function list_sites($dcs)
{
    print "Sites collection exists with the following sites:\n";

    $sites = $dcs->sites;
    $cursor = $sites->find();
    foreach ($cursor as $site) {
        printf ("%5s - %10s: %s\n", $site['_id'], $site['nesdisId'], $site['siteName']);
    }
}

function create_sites($dcs)
{
    print "  -- Creating the sites collection\n";
    
    $res = $dcs->createCollection("sites");
    $sitecol = $dcs->selectCollection("sites");

    $sites = json_decode(file_get_contents("etc/sites.json"), true);
    $res = $sitecol->insertMany($sites);
}

init_db();