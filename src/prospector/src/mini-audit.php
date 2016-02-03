<?php

require_once 'html.php';
require_once 'logger.php';

class LPOP_Mini_Audit
{
    public $domain;

    public $keyword;
    public $location;
    public $created;
    public $userid;

    public $record;

    public $post_id = -1;
    public $post_metadata = array();

    public $failure = 0;
        
    function __construct( $record )
    {
    }

    function _init()
    {
    }

    /**
     * We have collected all the info we need to create our mini-audit
     * custom post.
     */
    function create_post( $record )
    {
        $this->record = $record;

        $content = $this->get_post_content();
        if ( $content === null ) {
	    $this->failure = "FAIL: Could create content type";
            return null;
        }

        $path = pathinfo( $record['report'] );
        $title = $path['filename'];
        if ( $title === null || $title === '' ) {

	    $this->failure = "FAIL: to find the report " . $record['domain'];
            return null;
        }

        $post = array(
            'comment_status'    => 'open',
            'post_name'         => $record['domain'],
            'post_status'       => 'pending',
            'post_title'        => $title,
            'post_type'         => 'miniaudit',
            'post_content'      => $content,
        );

        $id = wp_insert_post( $post, true );
        if ( is_object( $id ) ) {

            $this->failure = print_r( $id, true );
            return null;

        }
	LPOP_Logger::logit("Created Audit $id: $title");

	$this->post_id = $id;

        /*
         * Now add the post meta data data
         */
        foreach ( $record as $k => $v ) {
            add_post_meta( $id, $k, $v );
        }
	return $id;
    }

    /**
     * This function will take the post report .html, strip out all of
     * markup styling and extra stuff we don't want from the report.
     * We'll then place it as one of our own WP reports.
     */
    function get_post_content()
    {
	libxml_clear_errors();

	$report = null;
	if ( !array_key_exists ( 'report', $this->record ) ) {
	    $this->failure =  "FAIL: no report file for miniaudit";
	}
	$report = $this->record['report'];
	
	if ( ( $report === null || $report === '' ) ||
	     ( $fp = fopen( $report, 'r' ) ) === null ) {
	    $this->failure =  "FAIL: can't open the report: $report ";
	    return null;
        }

        $htmlstr = fread( $fp, filesize($report) );
	if ( $htmlstr === null || $htmlstr === '') {
	    $this->failure =  "FAIL: could not open report : $report";
	    return null;
	}

        /*
         * get or original doc and our contents
         */
	$doc = new DOMDocument();
	$ret = $doc->loadHTML($htmlstr);
	if ( $ret === false ) {

	    libxml_use_internal_errors( false );
	    $this->failure = "FAILED to create XML from $report ";
	    return null;
	}
	$ele = $doc->documentElement;
	
        $c = $doc->getElementById( 'contents' );
	if ( $c == null ) {
	    $this->failure = "FAIL: Could not find content for report";
	    return null;
	}

        /*
         * create a new dom and the corresponding dom
         */
        $ndoc = new DOMDocument();
        $ndoc->formatOutput = true;
        $nele = $ndoc->documentElement;

        /*
         * create a new node for us to stick our dup's nodes into
         */
        $pdiv = $ndoc->createElement( 'div' );
        $ndoc->appendChild( $pdiv );

        /*
         * We need to skip some header nodes & so forth to get to the
         * div nodes that we really care about
         */
        $div = $c->firstChild;
        for ( $i = 0 ; $i < 4; $i++ ) { 
            $div = $div->nextSibling;   /* Skip these nodes text */
        }        

        for ( ; $div ; $div = $div->nextSibling ) { 

            $ndiv = $ndoc->importNode( $div, true );
            $pdiv->appendChild( $ndiv );
        }

        $nhtml = $ndoc->saveHTML();
        return $nhtml;
    }
}

?>