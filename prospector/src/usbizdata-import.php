<?php
/*
 * This import function is NOT general purpose yet.  It is a hack to
 * specifically import data from the US Biz Data lists for General
 * Contractors in 2013.
 */

require_once 'db.php';

$importdir = "D:/US_General_Contractor_Database";
$files_processed = 0;
$records_processed = 0;
$files_with_errors = 0;
$total_errors = 0;

$csv2sql = array(

    'Company Name'              => 'companyName',
    'Email'                     => 'companyEmail',
    'SIC Code'                  => 'sicCode',
    'SIC Code Description'      => 'sicCodeDescription',
    'SIC Code6'                 => 'sicCode6',
    'SIC Code 6'                => 'sicCode6',
    'SIC Code6 Description'     => 'sicCode6Description',
    'SIC Code 6 Description'    => 'sicCode6Description',
    'NAICS Code'                => 'naicsCode',
    'Contact Name'              => 'contactName',
    'First Name'                => 'firstName',
    'Last Name'                 => 'lastName',
    'Title'                     => 'title',
    'Address'                   => 'address',
    'Address2'                  => 'address2',
    'City'                      => 'city',
    'State'                     => 'state',
    'Zip'                       => 'zip',
    'Phone'                     => 'phone',
    'Fax'                       => 'fax',
    'Company Website'           => 'companyWebsite',
    'Revenue'                   => 'revenue',
    'Annual Revenue'            => 'revenue',
    'Employees'                 => 'employees',
    'Industry'                  => 'industry',
    'Desc'                      => 'description',
    'County'                    => 'county'
);

if ( ! is_dir( $importdir ) ) {
    die ("ERROR $importdir is not a directory<br/>");
}

$dbh = db_connect('rusty', '3st00ges');
    
$fs = scandir($importdir);
foreach ( $fs as $f ) {
    if ($f === '.' || $f === '..') {
        continue;
    }
    $files_processed ++;
    $err = process_csv( $f );
    if ( $err ) {
        $files_with_errors ++;
    }
}

$dbh = null;

function process_csv( $csvfile )
{
    global $csv2sql;
    global $importdir;

    $err = 0;

    $path = $importdir . '/' . $csvfile;
    
    echo "Processing: $csvfile.. ";                    

    $handle = fopen( $path, 'r' );
    if ( $handle == FALSE ) {
        die ( "Could not open file $path <br/>" );
        //return 1;
    }

    // Get the first row to determine the array headers
    $hdata = fgetcsv( $handle );
    if ( $hdata === false ) {
        die ("Could not get the headers");
    }

    $headers = array();
    foreach ( $hdata as $h ) {

        if ( ! array_key_exists ( $h, $csv2sql ) ) {
            die ( "CSV Header: $h does not exist <br/>" );
        }

        $sqlheader = $csv2sql[$h];
        $headers[] = $sqlheader;
    }

    $contractors = array();
    while ( ( $line = fgetcsv( $handle ) ) !== false ) {

        $contractor = array();

        for ( $c = 0; $c < count ($line) ; $c++ ) {
            
            $h = $headers[$c];
            $contractor[$h] = $line[$c];
        }

        $contractors[] = $contractor;
    }

    fclose( $handle );

    echo count($contractors) . " records processed<br/>";

    db_add_contractors( $contractors );
        
    return $err;
}

function db_add_contractors( $contractors )
{

    $nadds = 0;
    foreach ( $contractors as $contractor ) {

        // We get a maximum execution time succeeded error..!
        db_insert( 'usbizdata', $contractor );
        $nadds ++;
    }

    echo "$nadds contractors added to database<br/>";
}

?>