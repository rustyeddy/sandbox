<?php 

$lpop_mini_audit_custom_post_types = array (
    'domain',
    'company_name',
    'address',
    'city',
    'state',
    'zip',
    'phone',
    'score',
    'ad_traffic_price',
    'ads_traffic',
    'se_traffic',
    'se_keywords',
    'ads_keywords',
    'ad_competitors',
    'google_analytics',
    'sr',
    "cta",
    "lps",
    "lpp",
    "dis",
    "cae",
    "loc",
    "sl",
    "rm",
    'keyword',
    'location',
    'when_scraped',
    'report'
);

/**
 * Create the Mini Audit Custom post type
 */
function lpop_register_mini_audit_post_type() 
{
    $maudit_labels = array(
        'name'          => 'Mini Audits',
        'singular_name' => "Mini Audit",
        'menu_name'     => "Mini Audits",
        'edit_item'     => 'Edit Mini Audit',
        'view_item'     => "View Mini Audit",
        'search_items'  => "Search Mini Audits",
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

    $maudit_args = array(
        'label'                 => 'Mini Audits',
        'labels'                => $maudit_labels,
        'description'           => "Mini AdWords Audit",
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'menu_position'         => 25,
        'supports'              => $supports,
        'taxonomies'            => array( 'keywords', 'location', 'category' ),
        'has_archive'           => true,
    );

    register_post_type( "miniaudit", $maudit_args );
}

/*
 * Register the sidebar for my genesis theme
 */
/* Moved to the lakepark theme
function lpop_register_sidebars()
{
    $lpop_mini_audit_sidebar = array(
	'id'		=> 'miniaudit-single-sidebar',
	'name'		=> 'Mini Audit Sidebar',
	'description'	=> "Shows up when viewing a mini audit",
	);
    
    genesis_register_sidebar ( $lpop_mini_audit_sidebar );
}

add_action( 'get_header', 'lpop_miniaudit_sidebar' );
function lpop_miniaudit_sidebar()
{
    if ( is_singular ( 'miniaudit' ) ) {
	remove_action( 'genesis_sidebar', 'genesis_do_sidebar' );
	add_action( 'genesis_sidebar', 'lpop_do_miniaudit_sidebar' );
    }
}

function lpop_do_miniaudit_sidebar()
{
    dynamic_sidebar( 'miniaudit-single-sidebar' );
}
*/
?>
