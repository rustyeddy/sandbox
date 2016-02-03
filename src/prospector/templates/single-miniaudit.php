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
add_action( 'genesis_meta', 'lpop_ma_post_content' );
function lpop_ma_post_content()
{
    remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
    remove_action( 'genesis_post_content', 'genesis_do_post_content' );

    add_action( 'genesis_entry_content', 'lpop_do_ma_post_content' );
    add_action( 'genesis_post_content', 'lpop_do_ma_post_content' );
}

/*
 * Just incase we want to change something up..
 */
function lpop_do_ma_post_content()
{
    echo "<div class='mini-audit-content'>";
    the_content( $post->post_content );
    echo "</div><!-- mini audit content -->";
}

genesis();
