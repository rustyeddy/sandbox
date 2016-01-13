<?php

require_once( ABSPATH . 'wp-admin/includes/file.php' );

require_once 'html.php' ;
require_once 'mini-audit.php'; 
require_once 'logger.php';

class LPOP_Mini_Audits
{
    // This is old stuff
    static private $logs = array();

    // Processing an uploaded zip file
    static private $dirs = array();
    static private $csvfiles = array();
    static private $htmlfiles = array();
    static private $unknownfiles = array();
    static private $remdirs = array();

    static public $all_records = array();

    // The upload and unzip process
    static private $keyword;
    static private $location;
    static private $zipfile;
    static private $auditdir;

    static private $mini_audits        = array();

    /**
     * this function we need to first unzip the zipped file, then
     * process the files withing the zipeed file, then we'll need
     * to create our custom post types, etc.
     */
    static public function process_mini_audit_entry( $entry )
    {
        self::$keyword  = $entry[1];
        self::$location = $entry[2];
        $zipfile  = $entry[3];

        $auditdir = self::unzip_file( $zipfile );
        if ( $ret === false ) {
	    LPOP_Logger::logit("FAIL: Prospector can not unzip $zipfile" );
            return;
        }

        // Save these variables for posterity
        self::$zipfile = $zipfile;
        self::$dirs[] = $auditdir;

	LPOP_Logger::logit( "Unzipped $zipfile ===> $auditdir " );

        /*
         * Process all the files in the directory and create a set
         * of records that we'll then use to create our mini audit
         * posts
         */
        self::process_audit_dirs( );

	/*
	 * Now march throuh all the records
	 */
        foreach( self::$all_records as $k => $record ) {

            /*
             * now we need to create our custom post..
             */
            $ma = new LPOP_Mini_Audit( );
	    $id = $ma->create_post( $record );

	    if ( $id === null || $id < 1 ) {
		LPOP_Logger::logit(" Failed to create mini audit for $k ");
	    }
        }
    }

    /**
     * Unzip the uploaded file and pass back the directory name
     * of the unzipped file
     */
    static public function unzip_file( $zipfile )
    {
        $parts = pathinfo($zipfile);

        $updir = wp_upload_dir();
        $ziprel = strstr($zipfile, 'gravity_forms');

        // Construct the name of the directory the zipfile was uploaded to 
        $from = $updir['basedir'] . '/' . $ziprel;

        // Construct the name of the directory to unzip the file 
        $paths = pathinfo($from);
        $basedir = $paths['dirname'];
        $filename = $paths['filename'];
        $to = $basedir;

        /*
         * Now we need to prepare the WP_Filesystem so we can access our
         * zip directory and files.
         */
        $url = wp_nonce_url('themes.php?page=example', 'example-theme-options');
        if (false === ($creds = request_filesystem_credentials($url, '',
                                                               false, false, null) ) ) {
            echo "Prospector error: creds failed for files system<br/>";
            return false;
        }
        if ( ! WP_Filesystem( $creds ) ) {
            echo "Prospector error: WP_Filesystem failed<br/>";
            return false;
        }

        $wperr = unzip_file( $from, $to );
        if ( true !== $wperr ) {
            print_r( $wperr );      /* XXX - Print this out better */
            return false;
        }

        // Success!!
        return $to;
    }

    /*
     * This function will walk a directory for PPCP .csv files and
     * turn the entire set of .csv files into a set of assoc records.
     */
    static function process_audit_dirs( )
    {
        /**
         * Scan the directories and determine the .csv files vs. the html
         * files.  Also the raw vs. the non-raw html files need to be matched
         * up with the records in the .csv files.
         */
        if ( ! is_array ( self::$dirs ) ) {
	    die ( "process dirs self::$dirs is not an array ");
	}

	while ( count ( self::$dirs ) > 0 ) {
	    $dir = array_shift( self::$dirs );
	    self::process_dir( $dir );
	}

        /*
         * Process the .csv files.
         */
        foreach ( self::$csvfiles as $csv ) {

            // Will append to $all_recordsn
            $records = self::process_csv( $csv );
        }
        
	LPOP_Logger::logit( "Total records processed: " . count(self::$all_records) );
    }

    /**
     * This function will scan a directory to find child directories, .csv and
     * .html files.  The directories and each type of file will be placed in it's
     * own respective list for later processing.
     */
    function process_dir( $dir )
    {
        $count = 0;
        $files = scandir($dir);

        foreach ($files as $r) {

	    // parse the path and determine the type of file
	    $parts = pathinfo( $r );
	    $b = $parts['basename'];
	    $e = $parts['extension'];
	    $f = $parts['filename'];

            if ($r === '.' or $r === '..') continue;
            
            // Ignore the raw directory for now ...
            if ( $r == 'raw') continue;

            // Ignore index.html
            if ( $r == 'index.html' ) continue;

	    // XXX Need to fix filenames with & in them
	    if ( $i = strchr( $r, '&' ) ) {
		$old = $dir . '/' . $r;
		$r = str_replace( '&', '+', $r);
		$new = $dir . '/' . $r;
		rename ( $old, $new );
	    }

            // Reasemble the path name
            $r = $dir . '/' . $r;

            // If it is a directory place it on a directory stack
            if ( is_dir( $r ) ) {
                self::$dirs[] = $r;
                continue;
            }

            // Stash the file into the appropraite array
            if ( $e == 'csv') {
                self::$csvfiles[] = $r;
            } elseif ( $e == 'html' ) {
                self::add_html_file( $r, $parts );
            } else {
                self::$unknownfiles[] = $r;
            }
        }
    
        self::$logs[] .= " processed $count files, finished<br/>";
    }

    /** 
     * Process all records in this .csv file
     */
    function process_csv( $csvfile )
    {
        global $ppcp_csv2sql;

        $i = 1;
        $path = $csvfile;
    
        $keyword = '';
        $date ='';
        $location = '';
    
        // Let's store the keyword, location and date, just for fun
        $b = pathinfo($path, PATHINFO_FILENAME);
        $parts = explode('+', $b);

        // The meta information
        if (count($parts) == 4) {
            $keyword = $parts[1];
            $location = $parts[2];
            $date = $parts[3];
        }
    
        $handle = fopen( $path, 'r' );
        if ( $handle == FALSE ) {
            die ( "Could not open file $path <br/>" );
        }

        /**
         * We could save this as a static array, however this method will
         * adapt in the event that PPCP changes it's output format, we are
         * not hard coding a specific order in the .csv file.
         */
        $hdata = fgetcsv( $handle );
        if ( $hdata === false ) {
            die ("Could not get the headers");
        }

        $headers = array();
        foreach ( $hdata as $h ) {
            $headers[] = $h;

            if ( ! array_key_exists ( $h, $ppcp_csv2sql ) ) {
                die ("We do not have an SQL mapping for header: $h<br/>");
            }
            $sql_headers[] = $ppcp_csv2sql[$h];
        }

        $noreps = 0;
        while ( ( $line = fgetcsv( $handle ) ) !== false ) {

            $record = array();

            // Map the new data with the specific info from the column
            for ( $c = 0; $c < count ($line) ; $c++ ) {
                $h = $headers[$c];
                $hdr = $ppcp_csv2sql[$h];

                $val = $line[$c]; /* incase we need special processing */

                if ( strpos( $val, '%' ) !== false ) {

                    $val = floatval ( $val );
                }

                $record[$hdr] = $val;
            }

            // Map the report meta data (keyword, location and date 
            $record['keyword'] = $keyword;
            $record['location'] = $location;
            $record['when_scraped'] = $date;

            $report = self::find_report( $record );
            if ( $report === null ) {
                $noreps ++;
                $record['report'] = "";
            } else {
                $record['report'] = $report;
            }

            /**
             * Now we'll store each record according to domain, this will
             * make it easier to find the .html report.
             */
            $domain = $record['domain'];

            /** We'll remove the www */
            $names = explode ( '.', $domain );
            if ( $names[0] === 'www' ) {

                array_shift ($names);

                $domain = implode ( '.', $names ) ;
                $record['domain'] = $domain;
            }

            if ( array_key_exists($domain, self::$all_records)) {

                /*
                 * TBD: Need to figure out a good way to handle the
                 * domain collision.
                 */
                self::$logs[] = "We have a matching domain: $domain<br/>";
                $domain = $domain . $i++;
            }
            self::$all_records[$domain] = $record;
        }

        if ( $noreps > 0 ) {
            self::$logs[] = "Could not find $noreps PPCP reports";
        }

        fclose( $handle );
        return self::$all_records;
    }

    function find_report( $rec )
    {
        $company_name = $rec['company_name'];
        $domain = $rec['domain'];

        $name = null;            
        if (array_key_exists($company_name, self::$htmlfiles)) {
            $name = $company_name;
        } elseif (array_key_exists($domain, self::$htmlfiles)) {
            $name = $domain;
        }
        
        if ($name === null) {
            return null;
        }
        $report = self::$htmlfiles[$name];
        return $report;
    }

    /**
     * $r is the full path relative to the records directory
     * $parts and the parts of the file.
     */
    function add_html_file( $r, $parts )
    {
        $b = $parts['basename'];
        $bizname = '';
        $regex = "/^(.*) (Adwords Mini Audit)/";
        if (preg_match($regex, $b, $matches)) {
            $bizname = $matches[1];
            self::$htmlfiles[$bizname] = $r;
        } else {
            echo "Error no match for HTML File: $r" . br();
        }
    }

    static public function recursive_remove ( $d )
    {
	if (is_dir($dir)) {
	    $objects = scandir($dir);
	    foreach ($objects as $object) {
		if ($object != "." && $object != "..") {
		    if (filetype($dir."/".$object) == "dir") {
			rrmdir($dir."/".$object);
		    } else {
			unlink($dir."/".$object);
		    }
		}
	    }
	    reset($objects);
	    rmdir($dir);
	}
    }
    
    /**
     * Create a listing of the mini audits from the Database
     */
}

/*
 * Print out the list of all the mini audits we have. This will also create
 * a .csv file of all the meta data.
 */
add_shortcode( 'miniaudit-reports', 'lpop_miniaudit_reports' );
function lpop_miniaudit_reports()
{
    $args = array(
	'posts_per_page' => 100,
	'numberposts'	=> 100,
	'post_type'	=> 'miniaudit',
	);
    
    $audits = get_posts( $args );

    print "Mini Audits: " . count($audits) . "<br/>";

    $csv[] = array('ID', 'company', 'domain', 'mini-audit', 'keyword',
		   'location', 'address', 'city', 'state', 'zip', 'phone');
    foreach ( $audits as $ma ) {

	$pl = post_permalink( $ma->ID );
	$ma->pl = $pl;

	$md = get_metadata( 'post', $ma->ID );
	if ( $md === false ) {
	    echo "AH bad Mini Audit meta data Crap!<br/>";
	    exit;
	}

	$meta = array();
	foreach ( $md as $k => $v ) {
	    $meta[$k] = $v[0];
	}

	$company = ( $ma->company_name == '-' ) ?
	    $meta['domain'] : $meta['company_name'];

	echo lpop_href ( $company, $meta['domain'] ) . ', ';
	echo lpop_href( 'Mini Audit', $pl );

	printf( "<br/>key / loc: %s, %s<br/>", $meta['keyword'], $meta['location']);

	printf ( "%s <br/>%s <br/>%s, %s<br/>",
		 $meta['address'], $meta['city'], $meta['state'], $meta['zip'] );

	$phone = $meta['phone'];
	if(  preg_match( '/^\+?\d?(\d{3})(\d{3})(\d{4})$/', $phone,  $matches ) )
	{
	    $phone = '(' . $matches[1] . ') ' . $matches[2] . '-' . $matches[3];
	}

	printf ("<br/>%s<br/><hr/>", $phone);
	$csv[] = array( $ma->ID, $company, $meta['domain'], $pl, $meta['keyword'], $meta['location'],
			$meta['address'], $meta['city'], $meta['state'], $meta['zip'], $phone );
    }

    //* Now write the .csv file to be downloaded
    $up = wp_upload_dir();

    $fname = 'mini-audit-info.csv';
    $fpath = $up['path'] . '/' . $fname;
    $url = $up['url'] . '/' . $fname;

    $fp = fopen ( $fpath, 'w' );
    foreach ( $csv as $line ) {
	fputcsv ($fp, $line);
    }

    fclose( $fp );

    echo lpop_href("CSV Download", $url);
    
}

/**
 * We can use the importer to import all of these domains
 */
$ppcp_csv2sql = array (
    "Domain"            => 'domain',
    "Company Name"      => 'company_name',
    "Address"           => 'address',
    "City"              => 'city',
    "State"             => 'state',
    "Zip Code"          => 'zip',
    "Phone"             => 'phone',
    "Score"             => 'score',
    "Ad Traffic Price"  => 'ad_traffic_price',
    "Ads Traffic"       => 'ads_traffic',
    "SE Traffic"        => 'se_traffic',
    "SE Keywords"       => 'se_keywords',
    "Ads Keywords"      => 'ads_keywords',
    "Ad Competitors"    => 'ad_competitors',
    "GA"                => 'google_analytics',
    "SR"                => 'sr',
    "CTA"               => "cta",
    "LPS"               => "lps",
    "LPP"               => "lpp",
    "DIS"               => "dis",
    "CAE"               => "cae",
    "LOC"               => "loc",
    "SL"                => "sl",
    "RM"                => "rm",

    'keyword'           => 'keyword',
    'location'          => 'location',
    'when_scraped'      => 'when_scraped',

    'report'            => 'report'
    );


?>
