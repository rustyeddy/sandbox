<?php
/*
   Plugin Name: Lake Park Siteman
   Plugin URI: http://LakeParkOnline.com/lakepark-siteman/
   Description: Glue together the membership between the members plugin and
   Gravity forms.
   Version: 0.1
   Author: Rusty Eddy
   Author URI: http://EddyConsulting.com
   License: Mine
*/
require_once( 'paths.php' );

// require the functions
require_once( LPO_LIB_DIR . 'forms.php' );
require_once( LPO_LIB_DIR . 'logger.php' );
require_once( LPO_LIB_DIR . 'login.php' );
require_once( LPO_LIB_DIR . 'sites.php' );
require_once( LPO_LIB_DIR . 'todo.php' );
require_once( LPO_LIB_DIR . 'shortcodes.php' );

/*
 * Register up some style shiiite
 */
add_action ( 'wp_enque_scripts', 'lpo_add_stylesheet' );
function lpo_add_stylesheet()
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
register_activation_hook ( __FILE__, 'lpo_create_site_post_type' );
register_deactivation_hook (  __FILE__, 'lpo_deactivate_site_post_type' );

/*
 * Set some options
 *
 * uninstall_remove_all_data: unregister lpo_site, remove all meta data.
 * uninstall_remove_everything: remove anything related to lpo site
 */

$lpo_options = array(
		     'uninstall_remove_all_data'	=> true,
		     );

