<?php

/*
 * A word about directories:
 *
 * rootdir: is the root of the directory structure we'll be reading from.
 * reldir: is the relative directory beyond the basedir
 *
 */

require_once 'lib/html.php';
require_once 'lib/PageMaker.php';
require_once 'vendor/php-markdown/Michelf/Markdown.inc.php';
//require_once 'vendor/php-markdown/Michelf/MarkdownExtra.php';

function load_a_class( $class )
{
    include 'vendor/php-markdown/Michelf/' . $class . '.php';
}

spl_autoload_register('load_a_class');

/*
 * Config some defaults
 */
$rootdir = '.';
$d = null;
$f = null;			/* File to read, if any */

$template = 'docs/home.html';	/* HTML tempate to display */
$style = 'css/style.css';	/* The stylesheet */

/*
 * Read the config file if it exists
 */
if ( file_exists ( 'config.php' ) )
{
    require_once 'config.php';
}

/*
 * Parse incoming args
 */
parse_str($_SERVER['QUERY_STRING']);

/*
 * Create our primary class, and read the working directory
 */
$pm = new PageMaker( $rootdir, $template, $style );

/*
 * $d is the relative directory we'll append to the rootdir
 */
$pm->read_directory( $d );

if (isset($f)) {
    $pm->process_file( $f );
} else {
    
}

/*
 * Make sure we have a template before we call one.
 */
if ( ! file_exists ( $template ) ) {
    die ( "We are homeless, gimme shelter, missing template " . $template );
}

include $template;

?>

