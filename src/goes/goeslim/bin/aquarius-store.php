#!/usr/bin/env php

<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/dependencies.php';

$ds = $container['dataFactory'];
$resp = $ds->storeMeasurements();
print $resp;