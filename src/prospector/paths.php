<?php

/* -------------------------------------------------------------------- */
define( 'LPOP_PROSPECTOR_VERSION', '0.1' );
define( 'LPOP_DIR', plugin_dir_path( __FILE__ ) );
define( 'LPOP_URL', plugin_dir_url( __FILE__ ) );
define( 'LPOP_SRC_DIR', plugin_dir_path( __FILE__ ) . 'src/' );
define( 'LPOP_CSS_DIR', plugin_dir_path( __FILE__ ) . 'css/' );
define( 'LPOP_TMP_DIR', plugin_dir_path( __FILE__ ) . 'tmp/' );

/* -------------------------------------------------------------------- */

set_include_path( get_include_path() .
                  PATH_SEPARATOR . LPOP_DIR .
                  PATH_SEPARATOR . LPOP_SRC_DIR );

?>