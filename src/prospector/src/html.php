<?php

function lpop_href( $anchor, $ref, $other=null )
{
    $html = "<a href='" . $ref ."'";

    if ( $other != null ) {
        $html .= $other;
    }

    $html .= ">" . $anchor . '</a>';
    return $html;
}

if ( ! function_exists ( 'br' ) ) {
    function br()
    {
	return "<br/>";
    }
}

function lpop_pr( $obj )
{
    print "<pre>";
    print_r( $obj );
    print "</pre>";
}

?>