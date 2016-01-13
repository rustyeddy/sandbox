<?php

require_once( LPO_LIB_DIR . 'forms-maps.php' );

/**
 * This is the value of the user for the website we are creating.
 * By default we will have at least one user, that will be the 
 * current user.
 */
add_filter( 'gform_field_firstname', 'lpo_site_user_firstname' );
function lpo_site_user_firstname( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_firstname;    
}

add_filter( 'gform_field_lastname', 'lpo_site_user_lastname' );
function lpo_site_user_lastname( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_lastname;    
}

add_filter( 'gform_field_website', 'lpo_site_user_website' );
function lpo_site_site_user_website( $first )
{
    $user = wp_getcurrentuserinfo();
    return $user->user_url;
}


/*
 * Create the LPO_site object and fill in some of the
 * post data.  We will have to update the meta data after
 * the post has been created, that will happen 
 */
add_filter( 'gform_post_data', 'lpo_prepare_site', 10, 3 );
function lpo_prepare_site ( $post_data, $form, $entry )
{
    global $lpo_forms_map;
    
    /*
     * Only change post type on our Site post forms
     */
    if ( ! array_key_exists ( $form [ 'id' ], $lpo_forms_map ) ) {
    
	lpo_logit( __FILE__ .
		  ': Looks like an update from a form we do not control [passing]: ' .
		   $form[ 'id' ] );
	
        return $post_data;
    }

    // Save the site for later.
    $site = new LPO_Site();
    LPO_Site::$active_site = $site;
    
    $ret = $site->populate_from_form( $entry );
    if ( $ret == null ) {
	return null;
    }

    // Setup some lpo_site custom post default info
    $post_data['post_type'] = 'lpo_site';
    $post_data['comment_status'] = 'open';
    $post_data['post_status'] = 'private';
    $post_data['post_content'] = " ";
  
    return $post_data;
}

/*
 * This function takes a newly submitted site form request and
 * creates a custom lpo_site type from it.
 */
add_action('gform_after_submission', 'lpo_process_gforms', 10, 2);
function lpo_process_gforms($entry, $form)
{
    $site = LPO_Site::$active_site;
    if ( $site === null ) {
	lpo_logit( "ERROR: " . __FILE__ . ":" . __LINE__ .
		   ": Active site was not set!");
	return;
    }

    // Setup the lpo_site custom post type
    $site->id = $entry[ 'post_id' ];

    $u = get_userdata( $entry[ 'created_by' ] );
    $site->post_meta[ '_lpo_sandbox' ] = $u->user_login . ".lpobox.com";

    $site->update_post_meta();
}

?>
