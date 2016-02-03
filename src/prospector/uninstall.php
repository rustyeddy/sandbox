<?php

// require the functions
require_once( 'paths.php' );

/*
 * TODO:
 *
 * + Add the uninstall hook
 */

/*
lpo_logit( __FILE__ . " has been called. WP_UNINSTALL_PLUGIN = " .
	   defined( 'WP_UNINSTALL_PLUGIN' ) );
*/

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

lpop_uninstall();
function lpop_uninstall()
{
}


?>

