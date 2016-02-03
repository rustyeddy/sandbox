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

lpo_logit( __FILE__ . " has been called. WP_UNINSTALL_PLUGIN = " .
	   defined( 'WP_UNINSTALL_PLUGIN' ) );

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

lpo_uninstall();

function lpo_uninstall()
{
    global $lpo_options;

    lpo_log( 'uninstalling' );
    
    /*
     * TBD: if the user has opted to have all lpo_project data removed.
     */
    if ( $lpo_options['uninstall_remove_all_data'] ) {
	lpo_remove_all_sites();
    }

    /*
     * TBD: unregister the lpo_project type.
     * XXX: I can not seem to find a way to unregister the custom post type
     */
    /*
    $ret = unregister_post_type( '_lpo_site' );
    deactivate_post_type();
    */
}

add_shortcode( 'lpo-delete-data', 'lpo_remove_all_sites' );
function lpo_remove_all_sites()
{
    $loop = lpo_get_site_loop();
    if ( $loop == null ) {

	$html .= "Rats something smells fishy, we could not the the Sites loop";
	return $html;
    }

    lpo_logit( __FUNCTION__ . ": removing all lpo_site posts" );

    // Loop through all the sites and print them as a list.
    while ( $loop->have_posts() ) {

    	$post = $loop->the_post();

	lpo_logit( '    removing post id: ' . $post->ID );
	wp_delete_post( $post->ID );

    }
}

?>

