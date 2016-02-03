<?php

  // require the functions
require_once( LPO_LIB_DIR . 'functions.php' );

/*
 * Sites are custom post types 'lpo_site', each site
 * maintains a few pieces of information regarding there.
 *
 * Can't decide if this should be a post or a page...
 *
 *  - ID
 *  - post_title        - The site title
 *  - post_author	- wp_user->ID
 *  - post_status	- private (with password)
 *  - post_name		- url for the post
 *
 * TODO: figure out how to limit this post to those with the
 *   x capabilities.
 *   x post formats ?
 *   x taxonomies -> categories
 *   x how to remove the post type..
 *   x Automatically set the capability on the site page
 *     to lpo_member.
 *   x
 *
 * NOTES:
 *   x We can create hidden fields if we want to hide from users
 *
 */
class LPO_Site
{
    static public $form_id;	// Hmm, how to populate?  Needs to change if form changes
    static public $active_site = null;
    
    public $post;		// The post this site is based on

    public $id;			// WP post ID
    public $wp_user_id;		// WP User ID
    public $wp_username;	// WP User name

    // Post meta data
    public $post_meta;		// Assoc of custom post metadata
    public $user_meta;		// Assoc of custom user metadata
    public $form_map;		// Map the meta_keys to the form entry

    function __construct( $id = null )
    {
	if ($id != null) {
	    $this->post = get_post($id);
	    $this->id = $id;
	}

	$this->__init();
    }

    function __init()
    {
	//
	// Note prefixing meta keys with an '_' underscore will hide 
	// the keys from the admin tab and the edit page.  See the function
	// reference for add_post_meta()
	//
	$this->post_meta = array(
	    
	    '_lpo_domain' => null,

	    '_lpo_form_id' => null,
	    '_lpo_entry_id' => null,

	    '_lpo_has_dropbox' => false,
	    '_lpo_dropbox_account' => null,
			     
	    '_lpo_sandbox' => null,
	    '_lpo_status' => 'sandbox',

	    '_lpo_theme_provider' => 'genesis',
	    '_lpo_theme_name' => '',	
	    );
    }

    /*
     * This function is called after we recieve a form submit request,
     * Will use the form data to update the post meta data will update
     * this structures post_meta array.
     */
    function populate_from_form( $entry )
    {
	global $lpo_forms_map;

	$this->id = $entry[ 'post_id' ];      
	$this->wp_user_id = $entry[ 'created_by' ];

	$form_id = $entry[ 'form_id' ];

	if ( ! array_key_exists( $form_id, $lpo_forms_map ) ) {

	    lpo_logit ( "Could not find form id: " . $form_id .
			" will not be able to create site post");

	    return null;
	}

	$map = $lpo_forms_map[ $form_id ];

	/*
	 * We'll gather together all of the meta keys and values
	 */
	foreach ( $entry as $fld => $v ) {

	    if ( array_key_exists ( $fld, $map ) ) {

		$metakey = $map->map[ $fld ];
		$this->post_meta[ $metakey ] = $v;
	    }
	}

	return $this->post_meta;
    }

    /*
     * Populate from DB, but only those keys already in our
     * post meta array
     */
    function populate_from_meta()
    {
	$meta = get_post_custom( $this->id );

	foreach ( $meta as $k => $v ) {
	    if ( array_key_exists ( $k, $this->post_meta ) ) {

		/*
		 * if we have any multivalue keys, we'll need to
		 * handle them seperately here.
		 */
		$this->post_meta[ $k ] = $v[0] ;
	    }
	}
    }

    /*
     * This function will be used to update the database. It will
     * Typically be called just after we have received an update
     * from the form submit.
     */
    function update_post_meta()
    {
	// Now let's start adding the post meta data
	foreach ($this->post_meta as $k => $v) {

	    /*
	     * Add a new meta (ensure the entry is unique) if the meta
	     * key does not already exist.  If the new meta key does
	     * exist update the existing entry with the new entry.
	     */
	    add_post_meta( $this->id, $k, $v, true ) or
		update_post_meta( $this->id, $k, $v);
	}
    }

    /*
     * this function can be used to populate the form with default
     * values from the database before we display the form.
     *
     * TODO:
     */
    function populate_form()
    {
	
    }

    function show_meta_table()
    {
	$html = lpo_table_from_array( $this->post_meta );
	return $html;
    }

}

/*
 * Create our new post type.
 *
 * XXX - I think this needs to be in the activation hook??
 */
add_action( 'init', 'lpo_create_site_post_type' );
function lpo_create_site_post_type( )
{
    $labels = array('name'		=> __( 'Sites' ),
		'singular_name'		=> __( 'Site' ),
		'menu_name'		=> __( 'Sites' ),
		'add_new'		=> __( 'Add New', 'site' ),
		);

    $supports = array(
		'title',
		'editor',
		'author',
		'custom-fields',
		'comments',
		'can_export',
		'excerpt',
		'revisions',
		'post-formats',
		'page-attributes',
	);
  
    $args = array(

	'description'		=> 'Sites are for members',
	'exclude_from_search'	=> true,

	'has_archive'		=> true,
	'hierarchical'		=> true,

	'labels'		=> $labels,

	'public'		=> true,
	'publicly_queryable'	=> true,

	'show_in_menu'		=> true,
	'show_in_admin_bar'	=> true,
	'show_ui'		=> true,

	'supports'		=> $supports,

	'rewrite'		=> array ( 'slug' => 'sites'),

	/* 'capability_type'	=> ???, */
	);
    
    $ret = register_post_type( 'lpo_site', $args );

    /*
     * The rewrite rules allow us to see a page from a site e.g.
     *
     *   http://example.com/sites/rustyeddy.com (or whatever).
     *
     * Without the rewrite rules we would get a "404 page not found" error.
     */
    flush_rewrite_rules();
}

function lpo_deactivate_site_post_type( )
{
    flush_rewrite_rules();
}

/*
 * Add the site list
 */
add_shortcode( 'sites', 'lpo_sites');
function lpo_get_site_loop()
{
    global $post;

    // pull up all lpo_sites
    $args = array(
		'post_type'	=> '_lpo_sites',
	);

    // Prepare the query parameters
    $current_user = wp_get_current_user();
    $a = "author=" . $current_user->ID;
    $args = array (
    	$author,
		'post_type' => 'lpo_site',
		'posts_per_page' => 10 );

    // Do the query and come up with all of the query sites
    $loop = new WP_Query( $args );
    if ( ! $loop->have_posts() ) {
    	return null;
    }

    return $loop;
}

function lpo_sites( $atts, $content = null )
{
    $loop = lpo_get_site_loop();
    if ( $loop == null ) {
	$html .= "You don't currently have any sites!";
	return $html;
    }

    // Loop through all the sites and print them as a list.
    $html .= '<ul>';
    while ( $loop->have_posts() ) {

    	$loop->the_post();
    	$plink = get_post_permalink();
    	$title = get_the_title();

    	$html .= "<li /><a href='" .$plink. "'>" . $title . '</a>';
    }

    $html .= '</ul>';

    // Cleanup after ourselves
    wp_reset_query();
    wp_reset_postdata();

    // Give 'em what they want!
    return $html;
}


?>
