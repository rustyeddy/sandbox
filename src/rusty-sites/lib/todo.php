<?php

  //
  // Get all posts with TODO's.
  //
add_shortcode( 'todo-list', 'lpo_todo_list');
function lpo_todo_list($atts, $content = null)
{
    // TODO: must be a way to posts and pages in one query?
    $args = array(
	'meta_key'	=> 'todo',
	'post_type'	=> 'post',
	);


    $html = "<div class='lpo-todo-list'>";
    $html .="<ul>";

    //
    // Create a list of posts with todos.  The list will have a pointer to the
    // viewable page.
    //
    $todos = get_posts( $args );
    foreach ($todos as $t) {
	$html .="<li class='lpo-todo-item'/>" . $t->post_title ;

	// Get the meta value
	$val = get_post_meta($t->ID, 'todo', true);

	$html .="<ul>" .
	    "<li/>Post: <a href='" . $t->post_name . "'>" . $t->post_name . "</a>".
	    "<li/>Description: ".$val.
	    "</ul>";
    }

    // This time do it with pages
    $args = array(
	'meta_key'	=> 'todo',
	'post_type'	=> 'page',
	);

    $todos = get_posts( $args );
    foreach ($todos as $t) {
	$html .="<li class='lpo-todo-item'/>" . $t->post_title ;

	// Get the meta value
	$val = get_post_meta($t->ID, 'todo', true);

	$html .="<ul>" .
	    "<li/>Page <a href='" . $t->post_name . "'>Article: " . $t->post_name . "</a>".
	    "<li/>".$val.
	    "</ul>";
    }

    $html .='</ul>';
    $html .='</div>';

    return $html;
}

?>