<?php

$config = array(
    'aquarius' 	  => 'aquarius.dev',
	'username'	  => 'username',
	'password'	  => 'password',
	'output'      => 'text',
	'newfiledir'  => '/srv/goesdata/dcsdata/',
    'archivedir'  => '/srv/goesdata/dcsarch/arch/',
    'stored'      => '/srv/goesdata/dcsarch/stored.csv',
	'faildir'     => '/srv/goesdata/dcsarch/failed/',
	'missing'     => '/srv/goesdata/dcsarch/missing-timeseries.txt',
	'sitesfile'	  => 'etc/sites.json',
	'shefMap'	  => 'etc/shefmap.json',
    'dcsdir'      => '/srv/DCSTOOL/',
	'dcsuser'     => 'dcsuser',

    'datagetter'  => '/Applications/LrgsClient/bin/getDcsMessages',
    'lrgs-sites'  => [
        "www.ilexengineering.com=16003 guest",
        "cdadata.wcda.noaa.gov=16003",
        "eddn.usgs.gov=16003",
        "cdabackup.wcda.noaa.gov=16003",
        "drot.wcda.noaa.gov=16003"
    ]
);
