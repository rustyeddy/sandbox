<?php

//spl_autoload_register();
set_include_path( get_include_path() . PATH_SEPARATOR . 'src' );

require_once( 'src/bootstrap.php' );

require_once( 'src/controller_mwp.php' );
require_once( 'vendor/jacwright/restserver/RestServer.php' );

$doapi = true;

if ( $doapi ) {
	$mode = 'debug';
	$server = new RestServer( $mode );

	$server->addClass('MWP_Controller');
	$server->addClass('WP_Sites', '/sites' );
	$server->handle();
} else {
	include 'html/home.html';
}

?>
