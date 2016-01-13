<?php
/*
   Plugin Name: Webman
   Plugin URI: http://LakeParkOnline.com/plugins/webman
   Description: Manage and monitor websites from a single WordPress site
   Version: 0.1
   Author: Rusty Eddy
   Author URI: http://EddyConsulting.com
   License: Mine
*/
require_once( 'paths.php' );

// require the functions
require_once( $webman_lib_dir . 'forms.php' );
require_once( $webman_lib_dir . 'logger.php' );
require_once( $webman_lib_dir . 'login.php' );
require_once( $webman_lib_dir . 'sites.php' );
require_once( $webman_lib_dir . 'todo.php' );
require_once( $webman_lib_dir . 'shortcodes.php' );

/*
 * Register up some style shiiite
 */
add_action ( 'wp_enque_scripts', 'webman_add_stylesheet' );
function webman_add_stylesheet()
{
    /*
     * XXX This is broke - we are still getting the style sheet from the themes
     * style.css
     */
    wp_regiser_style( 'prefix-style', plugins_url( 'css/lakepark.css', __FILE__  ) );
    wp_enqueue_style( 'prefix-style' );
}


/*
 * Get ready for activation and deactivation
 */
register_activation_hook ( __FILE__, 'webman_create_site_post_type' );
register_deactivation_hook (  __FILE__, 'webman_deactivate_site_post_type' );

/*
 * Set some options
 *
 * uninstall_remove_all_data: unregister webman_site, remove all meta data.
 * uninstall_remove_everything: remove anything related to webman site
 */
$webman_options = array(
		     'uninstall_remove_all_data'	=> true,
		     );

