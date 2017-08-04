#!/usr/bin/env php

<?php

require_once("src/utilities.php");

$units = [];
$csv = \SierraHydrog\Goes\decode_csv_file("etc/params-units.csv");
foreach ($csv as $a) {
    $units[$a['short']] = $a['units'];
}

$sites = json_decode(file_get_contents("etc/sites.json"), true);
/*
foreach ($tsd as $j) {
    $tsid = $j['Identifier'];
    if (!strstr($tsid, "GOES")) continue;
    $unit = $j['Unit'];
    //echo "timeseries create '$tsid'  unit=$unit\n";
    echo $tsid . " units=$unit\n";
}
*/

foreach ($sites as $s) {
    foreach ($s['timeseriesIds'] as $tsid) {
        echo $tsid . ".GOES@". $s['mnemonic'];
        echo " units=" . $units[$tsid] . "\n";
    }
}

