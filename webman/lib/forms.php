<?php

require_once( WEBMAN_LIB_DIR . 'forms-maps.php' );

/**
 * This is the value of the user for the website we are creating.
 * By default we will have at least one user, that will be the 
 * current user.
 */
add_filter( 'gform_field_firstname', 'webman_site_user_firstname' );
function webman_site_user_firstname( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_firstname;    
}

add_filter( 'gform_field_lastname', 'webman_site_user_lastname' );
function webman_site_user_lastname( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_lastname;    
}

add_filter( 'gform_field_website', 'webman_site_user_website' );
function webman_site_site_user_website( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_url;
}


/*
 * Create the WEBMAN_site object and fill in some of the
 * post data.  We will have to update the meta data after
 * the post has been created, that will happen 
 */
add_filter( 'gform_post_data', 'webman_prepare_site', 10, 3 );
function webman_prepare_site ( $post_data, $form, $entry )
{
    global $webman_forms_map;
    
    /*
     * Only change post type on our Site post forms
     */
    if ( ! array_key_exists ( $form [ 'id' ], $webman_forms_map ) ) {
    
	webman_logit( __FILE__ .
		  ': Looks like an update from a form we do not control [passing]: ' .
		   $form[ 'id' ] );
	
        return $post_data;
    }

    // Save the site for later.
    $site = new WEBMAN_Site();
    WEBMAN_Site::$active_site = $site;
    
    $ret = $site->populate_from_form( $entry );
    if ( $ret == null ) {
	return null;
    }

    // Setup some webman_site custom post default info
    $post_data['post_type'] = 'webman_site';
    $post_data['comment_status'] = 'open';
    $post_data['post_status'] = 'private';
    $post_data['post_content'] = " ";
  
    return $post_data;
}

/*
 * This function takes a newly submitted site form request and
 * creates a custom webman_site type from it.
 */
add_action('gform_after_submission', 'webman_process_gforms', 10, 2);
function webman_process_gforms($entry, $form)
{
    $site = WEBMAN_Site::$active_site;
    if ( $site === null ) {
	webman_logit( "ERROR: " . __FILE__ . ":" . __LINE__ .
		   ": Active site was not set!");
	return;
    }

    // Setup the webman_site custom post type
    $site->id = $entry[ 'post_id' ];

    $u = get_userdata( $entry[ 'created_by' ] );
    $site->post_meta[ '_webman_sandbox' ] = $u->user_login . ".webman.com";

    $site->update_post_meta();
}

?>
