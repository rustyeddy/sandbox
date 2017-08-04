#!/usr/bin/env php

<?php

require 'vendor/autoload.php';

$opts = $lopts = [
    "sites-count",
    "sites-drop",
    "sites-list",
    "sites-load:",              // file: etc/sites.json
];

$opts = getopt("", $lopts);

$mon = new MongoDB\Client('mongodb://localhost');
$goes = $mon->goes;

foreach ($opts as $k => $v) {
    switch ($k) {
    case 'sites-count':
        $count = sites_count();
        if ($count)
            echo $count . " sites in the collection.\n";
        else
            echo "Sites collection is empty\n";
        break;

    case 'sites-list':
        sites_list();
        break;

    case 'sites-load':
        sites_load($v);
        break;

    case 'sites-drop':
        sites_drop();
        break;

    default:
        echo "Unkown option: $k\n";
        exit(-1);
    }
}
print "\n";
exit(0);

function sites_list()
{
    global $goes;

    if (sites_count() == 0) {
        print "The sites collection is empty or does not exist\n";
        return;
    }
        
    print "Sites collection exists with the following sites:\n";

    $sites = $goes->sites;
    $cursor = $sites->find();
    foreach ($cursor as $site) {
        printf ("%5s - %10s: %s\n", $site['_id'], $site['nesdisId'], $site['siteName']);
    }
}

function sites_count()
{
    global $goes;
    $found = false;
    $count = 0;

    $cols = $goes->listCollections();
    foreach ($cols as $cinfo) {
        if ($cinfo->getName() == "sites") {
            $sites = $goes->sites;
            $count = $sites->count();
            $found = true;
        }
    }
    return $count;
}

function sites_load($fname)
{
    global $goes;

    $count = sites_count();
    if ($count > 0) {
        echo "Sites already exists, please 'drop-sites' before loading again\n";
        exit(-2);
    }
    print "Creating the sites collection: ";
    
    $res = $goes->createCollection("sites");
    $sitecol = $goes->selectCollection("sites");

    if (!file_exists($fname)) {
        print "Sites file does not exist: $fname\n";
        exit(-3);
    }

    $sites = json_decode(file_get_contents("etc/sites.json"), true);
    $res = $sitecol->insertMany($sites);

    $count = $res->getInsertedCount();
    echo "Sites inserted: $count \n";

    echo "Creating indexes for sites\n";
    $res = sites_create_indexes();

    return $res;
}

function sites_create_indexes()
{
    global $goes;
    $res = $goes->sites->createIndex([ 'nesdisId' => 1 ], [ 'unique' => 1 ]);
    print_r($res);
    return $res;
}


function sites_drop()
{
    global $goes;

    echo "Dropping the sites collection.\n";

    $sites = $goes->sites;
    $res = $sites->drop();
    return $res;
}
