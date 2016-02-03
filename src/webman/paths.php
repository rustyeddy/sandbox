<?php

/* -------------------------------------------------------------------- */
define( 'WEBMAN_SITES_VERSION', '0.1' );
define( "WEBMAN_DIR", plugin_dir_path( __FILE__ ) );
define( 'WEBMAN_URL', plugin_dir_url( __FILE__ ) );
define( "WEBMAN_LIB_DIR", plugin_dir_path( __FILE__ ) . 'lib/' );
/* -------------------------------------------------------------------- */

$webman_lib_dir = plugin_dir_path( __FILE__ ) . 'lib/' ;


set_include_path( get_include_path() .
		  PATH_SEPARATOR . WEBMAN_DIR .
		  PATH_SEPARATOR . WEBMAN_LIB_DIR );

?>