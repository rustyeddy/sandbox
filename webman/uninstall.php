<?php

  // require the functions
require_once( 'paths.php' );
require_once( 'sites.php' );
require_once( 'logger.php' );

  /*
   * TODO:
   *
   * x Add the uninstall hook.
   */

webman_logit( __FILE__ . " has been called. WP_UNINSTALL_PLUGIN = " .
	   defined( 'WP_UNINSTALL_PLUGIN' ) );

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

webman_uninstall();

function webman_uninstall()
{
    global $webman_options;

    webman_log( 'uninstalling' );
    
    /*
     * TBD: if the user has opted to have all webman_project data removed.
     */
    if ( $webman_options['uninstall_remove_all_data'] ) {
	webman_remove_all_sites();
    }

    /*
     * TBD: unregister the webman_project type.
     * XXX: I can not seem to find a way to unregister the custom post type
     */
    /*
    $ret = unregister_post_type( '_webman_site' );
    deactivate_post_type();
    */
}

add_shortcode( 'webman-delete-data', 'webman_remove_all_sites' );
function webman_remove_all_sites()
{
    $loop = webman_get_site_loop();
    if ( $loop == null ) {

	$html .= "Rats something smells fishy, we could not the the Sites loop";
	return $html;
    }

    webman_logit( __FUNCTION__ . ": removing all webman_site posts" );

    // Loop through all the sites and print them as a list.
    while ( $loop->have_posts() ) {

    	$post = $loop->the_post();

	webman_logit( '    removing post id: ' . $post->ID );
	wp_delete_post( $post->ID );

    }
}

?>

