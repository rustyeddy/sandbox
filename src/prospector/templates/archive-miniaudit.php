<?php

// Add custom body class
add_filter( 'body_class', 'lpop_miniaudit_add_body_class' );
function lpop_miniaudit_add_body_class( $classes )
{
    $classes[] = 'miniaudit-body';
    return $classes;
}

/*
 * remove the page title and the breadcrumbs
 */
add_action( 'genesis_meta', 'lpop_ma_archive_content' );
function lpop_ma_archive_content()
{
    remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
    remove_action( 'genesis_loop', 'genesis_do_loop' );

    add_action( 'genesis_entry_content', 'lpop_do_ma_archive_content' );
    add_action( 'genesis_post_content', 'lpop_do_ma_archive_content' );
    add_action( 'genesis_loop', 'lpop_do_ma_loop' );

}

function lpop_do_ma_loop()
{
    if ( !is_user_logged_in() ) {

	// XXX: Replace this with an entry form for the permalink
	echo "<h2>tough luck bro</h2>";
    } else {

	// XXX: check to see if we are at least an admin.
	lpop_ma_archive_loop(); // Make this part of an entry content	
    }
}

function lpop_ma_archive_loop()
{
    //* Use old loop hook structure if not supporting HTML5
    if ( ! genesis_html5() ) {
	genesis_legacy_loop();
	return;
    }

    do_action( 'genesis_before_entry' );

    printf( '<article %s>', genesis_attr( 'entry' ) );

    do_action( 'genesis_entry_header' );

    do_action( 'genesis_before_entry_content' );

    printf( '<div %s>', genesis_attr( 'entry-content' ) );

    lpop_miniaudit_reports(); // Make this part of an entry content	
    
    echo '</div>'; //* end .entry-content

    do_action( 'genesis_after_entry_content' );

    do_action( 'genesis_entry_footer' );

    echo '</article>';

    do_action( 'genesis_after_entry' );
}

function lpop_do_ma_archive_content()
{

}

genesis();