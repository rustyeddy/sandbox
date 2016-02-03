<?php

  /*
   * List of short codes:
   */


add_shortcode( 'webman', 'webman_webman');
function webman_webman($atts, $content = null)
{
    // set the attributes defaults and prepare to get them
    $atts = array(
	'foo'		=> site_url (''),
	);

    // Get the attributes we'll be using
    extract (shortcode_atts ($atts, $atts));

    echo "Do What Webman? <br/>";
}


// Just to trigger interesting things
add_shortcode( 'webman-debug', 'webman_debug');
function webman_debug($atts, $content = null)
{
    echo '<h2>Reregistering Post Type</h2>';

    $args = array(
	'meta_key'	=> 'todo',
	'post_type'	=> 'post',
	);
}


// Just to trigger interesting things
add_shortcode( 'div', 'lpo_div');
function lpo_div($atts, $content = null)
{
    $args = array(
	'id'	=> null,
	'class'	=> 'lpo-div',

	/*
	 capabilities	=> null,
	 roles		=> null,
	 hide		=> false,
	*/
	);

    // Get the attributes we'll be using
    extract (shortcode_atts ($atts, $atts));

    $h = "<div";
    if (array_key_exists(id, $atts) && $atts['id'] != null) {
	$h .= " id='" . $atts['id'] . "'";
    }

    $h .= " class='" . $atts['class'] . "'>";

    $h .= do_shortcode( $content );
    $h .= '</div>';

    return $h;
}

// Just to trigger interesting things
add_shortcode( 'username', 'lpo_username');
function lpo_username($atts, $content = null)
{
    global $current_user;
    get_currentuserinfo();
  
    if ($current_user === null) {

	/*
	 * XXX Something went wrong!  An unregistered user should have never
	 * been able to get this far.
	 */
	return "FAILED";
    }

    return $current_user->user_login;
}

// Just to trigger interesting things
add_shortcode( 'script', 'lpo_script');
function lpo_script($atts, $content = null)
{
    // set the attributes defaults and prepare to get them
    $def = array(
	'type'		=> 'text/javascript',
	'src'		=> '',
	);

    // Get the attributes we'll be using
    extract (shortcode_atts ($def, $atts));

    $html = "<script type='".$atts['type']."' src='".$atts['src']."'>";
    $html .= "</script>";

    return $html;
}

?>