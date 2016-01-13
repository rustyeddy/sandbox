<?php
/*
 * The base import class
 */
class Importer
{
    public $importdir          = '';

    public $csv2sql;                 /* the csv to sql map */
    public $db_table;                /* the db table to use */

    public $dbh;

    // Some stats
    public $files_processed    = 0;
    public $records_processed  = 0;
    public $files_with_errors  = 0;
    public $total_errors       = 0;

    // This function probably belongs somewhere else
    function db_connect()
    {
        $dbh = db_connect('rusty', '3st00ges');
    }

    /**
     * Process a directory with a bunch of .csv files
     */
    function process_dir( $dir )
    {
        if ( ! is_dir( $importdir ) ) {
            die ("ERROR $importdir is not a directory<br/>");
        }

        $this->importdir = $dir;

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
    }

    /*
     * Process a csvfile and insert the records into the
     * corresponding database table
     */
    function process_csv( $csvfile )
    {
        $err = 0;

        //$path = $this->importdir . '/' . $csvfile;
        $path = $csvfile;
    
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

            if ( ! array_key_exists ( $h, $this->csv2sql ) ) {
                die ( "CSV Header: $h does not exist <br/>" );
            }

            $sqlheader = $this->csv2sql[$h];
            $headers[] = $sqlheader;
        }

        $records = array();
        while ( ( $line = fgetcsv( $handle ) ) !== false ) {

            $record = array();

            for ( $c = 0; $c < count ($line) ; $c++ ) {
            
                $h = $headers[$c];
                $record[$h] = $line[$c];
            }

            $records[] = $record;
        }

        fclose( $handle );

        echo count($records) . " records processed<br/>";

        //db_add_records( $records );
        
        return $records;
    }

    function db_add_records( $records )
    {
        $nadds = 0;
        foreach ( $records as $record ) {
            
            // We get a maximum execution time succeeded error..!
            db_insert( 'usbizdata', $record );
            $nadds ++;
        }

        echo "$nadds records added to database<br/>";
    }
}

?>
