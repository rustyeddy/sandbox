<?php

/*
 * Todo: move this value into the wp_options table - make it
 * configurable via a menu item
 */
class LPOP_Logger
{
    static public $logs = array( );
    static public $meta = array( 'comment_post_ID' => '3213' );

    static public function start_logging()
    {
	/*
	 * If we happen to call start logging before we flushed
	 * let's just flush the previous session and start new.

	 */
	self::flush_logs();
    }

    static public function flush()
    {
	if ( count ( self::$logs ) < 0 ) {
	    return;
	}

	$comment = '';
	foreach ( self::$logs as $line ) {
	    $comment .= $line . '<br/>';
	}
	self::$meta['comment_content'] = $comment;
	self::$meta['approved'] = true;

	$user = wp_get_current_user();
	self::$meta['comment_author'] = $user->user_login;
	self::$meta['comment_author_email'] = $user->user_email;
	self::$meta['comment_author_email'] = $user->user_email;
	self::$meta['comment_author_email'] = $user->ID;

	/*
	 * now write the content to the page.
	 */
	wp_insert_comment( self::$meta );

	// Resent the logs.
	self::$logs = array();
    }

    static public function logit( $str )
    {
	self::$logs[] = $str;
    }

    static public function pre( $var )
    {
	$html = '<pre>';
	$html .= print_r($var, true);
	$html .= '</pre>';

	self::$logs[] = $str;
    }

    /*
     * On fail we'll flush and exit
     */
    static public function fail( $str )
    {
	self::$logs[] = $str;
    }

    static public function dump( )
    {
	$comment = '';
	foreach ( $logs as $line ) {
	    $comment .= $line . '<br/>';
	}
	echo $comment;
    }
}

?>