<?php

/*
 * Add the template types redirects for the mini audit types
 */
add_action("template_redirect", 'lpop_theme_redirect');
function lpop_theme_redirect()
{
    global $wp;
    $plugindir = dirname( __FILE__ );

    if ($wp->query_vars["post_type"] == 'miniaudit') {

        // A Specific Custom Post Type
	$templatefilename = ( array_key_exists ( 'name', $wp->query_vars ) ) ?
	    'single-miniaudit.php' :
	    'archive-miniaudit.php';
	
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
	    $return_template = $plugindir . '/' . $templatefilename;
        }
        do_theme_redirect($return_template);

    } elseif ($wp->query_vars["taxonomy"] == 'miniaudit_categories') {

        // A Custom Taxonomy Page
        $templatefilename = 'taxonomy-miniaudit_categories.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
	    $return_template = $plugindir . '/' . $templatefilename;
        }
        do_theme_redirect($return_template);

    } elseif ($wp->query_vars["pagename"] == 'mini-audit-control-center') {

        // A Simple Page
        $templatefilename = 'page-mini-audit-control-center.php';
        if (file_exists(TEMPLATEPATH . '/' . $templatefilename)) {
            $return_template = TEMPLATEPATH . '/' . $templatefilename;
        } else {
	    $return_template = $plugindir . '/' . $templatefilename;
        }
        do_theme_redirect($return_template);
    } 
}

function do_theme_redirect($url)
{
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}

?>