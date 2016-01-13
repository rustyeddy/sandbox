<?php

/* -------------------------------------------------------------------- */
define( 'LPO_SITES_VERSION', '0.1' );
define( 'LPO_DIR', plugin_dir_path( __FILE__ ) );
define( 'LPO_URL', plugin_dir_url( __FILE__ ) );
define( 'LPO_LIB_DIR', plugin_dir_path( __FILE__ ) . 'lib/' );
/* -------------------------------------------------------------------- */

set_include_path( get_include_path() .
		  PATH_SEPARATOR . LPO_DIR .
		  PATH_SEPARATOR . LPO_LIB_DIR );

?>