<?php

// Add custom body class
add_filter( 'body_class', 'lpop_miniaudit_add_body_class' );
function lpop_miniaudit_add_body_class( $classes )
{
    $classes[] = 'macc-body';
    return $classes;
}

add_action( 'genesis_meta', 'lpop_macc_content' );
function lpop_macc_content()
{
    remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
    remove_action( 'genesis_post_content', 'genesis_do_post_content' );

    add_action( 'genesis_entry_content', 'lpop_do_macc_content' );
    add_action( 'genesis_post_content', 'lpop_do_macc_content' );
}

function lpop_do_macc_content()
{
    the_content();
    
    $html .= "<br/><h2>Last Message</h2>";
    $comment = lpop_get_last_comment( $id );
    $html .= $comment->comment_date . "<br/>";
    $html .= $comment->comment_content . "<br/>";

    echo $html;
}

function lpop_get_last_comment( )
{
    $args = array(
	'post_id'	=> get_the_ID(),
	'number'	=> 1,
	);

    $comments = get_comments( $args );
    return $comments[0];
}

genesis();