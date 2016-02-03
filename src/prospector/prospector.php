<?php
/*
   Plugin Name: LPO Prospector
   Plugin URI: http://LakeParkOnline.com
   Description: This plugin will help you track prospects.
   Version: 0.643
   Author: Rusty Eddy
   Author URI: http://EddyConsulting.com
   License: Mine
*/
require_once( 'paths.php' );
require_once( 'src/mini-audit.php' );
require_once( 'src/mini-audit-post-type.php' );
require_once( 'src/gforms-support.php' );
require_once( 'templates/template-init.php' );

/*
 * Register the mini_audit post type
 */
add_action( 'init', 'lpop_init' );

/*
 * XXX - This needs to turn into a setting option!
 */
$lpop_miniaudit_upload_form_id = 3;

/*
 * Initialize gforms
 */
function lpop_init()
{
    lpop_register_mini_audit_post_type();
    lpop_gforms_init();
}

add_action( 'wp_enqueue_scripts', 'lpop_add_mini_audit_style' );
function lpop_add_mini_audit_style()
{
    wp_register_style( 'mini-audit', plugins_url('css/mini-audit.css', __FILE__ ) );
    wp_enqueue_style( 'mini-audit' );
}

/*
 * Setup for activiation and deactivation
 */
register_activation_hook ( __FILE__, 'lpop_activate' );
register_deactivation_hook (  __FILE__, 'lpop_deactivate' );

function lpop_activate()
{
    lpop_init();
    flush_rewrite_rules();	/* we want our permalinks to work right? */
}

/*
 * Short codes
 */
add_shortcode( 'lpop-gform-entries', 'lpop_gform_get_entry' );

