<?php

/**
 * This file is used to bootstrap elements used by the mwp command and
 * the phpunit tests.
 */
error_reporting ( E_ALL );

require 'vendor/autoload.php';

require_once 'configurator.php';
require_once 'command.php';
require_once 'mysql.php';
require_once 'mongo.php';
require_once 'wp-sites.php';
require_once 'wp-site.php';

/*
 * Set up some globals and add commands
 * ------------------------------------------------------ */
$commands       = array();
$databases      = array();
$sites          = array();

/*
 * Some commands we may want to access
 * ------------------------------------------------------- */
$mongo			= new MongoMWP();		/* Create a connection to mongoDB */
$config         = new Configurator();   /* read the config file */
$help           = new HelpCmd();        /* setup the help commands */
$mysql          = new MySQL();          /* manage databases */
$scanner        = new WP_Sites();       /* WP Site Scanner */
$wp             = new WP_CLI();         /* Wrapper for wp-cli */

new ExitCmd();                          /* exit */
$verbose = 0;

?>


