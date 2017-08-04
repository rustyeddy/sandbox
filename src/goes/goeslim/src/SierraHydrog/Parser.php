<?php

namespace SierraHydrog;

use Carbon\Carbon;

class Parser {
    use ContainerTrait;

    private static $instance = null;

    protected $standard = true;

    /**
     * constructor
     */
    public function __construct() {
    }

    /**
     * The header is the section of the data file that encodes the
     * NESDIS ID, time and a few other things.  The string will take
     * the format like this:
     *
     *    6A10057615335144024G47-1NN038WUB00083
     *
     * The format of the header is described on this we page:
     *
     *    http://eddn.usgs.gov/dcpformat.html
     *
     * Format is as follows:
     *
     * 8 hex digit DCP Address
     * YYDDDHHMMSS – Time the message arrived at the Wallops receive station. 
     *               The day is represented as a three digit day of the year
     *               (julian day).
     *
     * 1 character failure code
     * 2 decimal digit signal strength
     * 2 decimal digit frequency offset
     * 1 character modulation index
     * 1 character data quality indicator
     * 3 decimal digit GOES receive channel
     * 1 character GOES spacecraft indicator (‘E’ or ‘W’)
     * 2 character data source code Data Source Code Table
     * 5 decimal digit message data length
     * 
     * @param  string $title the title string from data file
     * @return boolean       true / false
     */
    public function parseHeader( $title ) {

        $header = array();

        /*
         * Get all the values from the header first, mess with them later.
         */

        // Get the nesdisid
        $header['nesdisid'] = substr($title, 0, 8);

        // Get the timestamp
        $year   = substr($title,  8, 2);
        $julian = substr($title, 10, 3);
        $hour   = substr($title, 13, 2);
        $minute = substr($title, 15, 2);
        $second = substr($title, 17, 2);

		// Goes records Julian days from 1-365/366, Carbon from 0-364/365.  We'll
		// Adjust for Carbon.
		$julian--;

        // Other data
        $header['failcode']   = substr($title, 19, 1);
        $header['sigstrenth'] = substr($title, 20, 2);
        $header['frequency']  = substr($title, 22, 2);
        $header['modulation'] = substr($title, 24, 1);
        $header['quality']    = substr($title, 25, 1);
        $header['channel']    = substr($title, 26, 3);
        $header['craft']      = substr($title, 29, 1);
        $header['source']     = substr($title, 30, 2);

        // Message length
        $header['length']     = substr($title, 32, 5);

        /*
         * Get the time stamp, first we need to add 2000 since we only
         * receive YY.  Then we need to convert the Julian day count
         * DDD to MM-DD.
         */
        $year += 2000;

        /*
         * We have the day count (julian) from the beginning of the year.
         * That means we need to determine the Julian date from the beginning
         * of the year and add the julian days we extracted from the header.
         *
         * The 'z' create format will assume julian days from the current year. That means
         * if we extract a day from the previous year, the createFromFormat() routine
         * below will incorrectly create a future date in this year, hence we need
         * to subtract that date from the current year.
         *
         * Example: today is 2/25/2015, we grab a file from 12/30/2015.  The julian day
         * is 364, the 'z' conversion will then create the date 12/30/2016 assuming we
         * are asking for the julian date of this year when we were asking the julian
         * date of last year.
         *
         * In that case, we subtract one year.
         */

        $c = Carbon::createFromFormat('z', $julian, 'UTC');
        $c->hour = $hour;
        $c->minute = $minute;
        $c->second = $second;
        if ($c->gt(Carbon::now())) {
            $c->subYear(1);
        }
        $timestamp = $c->toDateTimeString();

        $header['timestamp'] = $timestamp;
        $header['carbon'] = $c;
        return $header;
    }

}

function getBodyParser($site) {
    global $parsers;
    $sid = $site->mnemonic;
    $idx = 'default';
    if (array_key_exists($sid, $parsers)) {
        $idx = $sid;
    }
    $p = $parsers[$idx];
    return($p);
}

global $parsers;
$parsers['BGF'] = function (&$data, $site) {
    global $log;

    $tslabel = "HG.GOES@BGF";

    // Don't need the keys: $keys = $site->getShefKeys();
    for ($i = 0; $i < 3; $i++) {
        $d = trim(array_shift($data));
        $meas[$tslabel][] = $d;
    }

    $d = array_shift($data);
    $foo = explode(' ', trim($d));
    $meas[$tslabel][] = $foo[0];
    $meas['VB.GOES@BGF'][] = $foo[1];
    return $meas;
};

$parsers['default'] = function( &$data, $site ) {
    assert($site != null);

    //$log = $this->logger;
    $timeseriesIds = $site->getTimeseriesIds();

    $newdata = array();
    $n = count($timeseriesIds);
    if ($n > count($data)) {
        /*
        $log->warn($site->getMnemonic() .
                   " key count is greater than the" .
                   "number of data lines we have ". $n .
                   " key count: " . count($data));
        */
        return false;
    }
    for ($i = 0; $i < $n; $i++) {

        $tsid = array_shift($timeseriesIds);
        $v = array_shift($data);
        $v = trim( $v );
        $vals = preg_split("/\s+/", $v);
        $volts = array();

        /*
         * Check to see if we have some type of battery voltage, just in case.
         */
        if (substr($tsid, 0, 2) == 'VB' ||
            substr($tsid, 0, 3) == 'YBL' ||
            substr($tsid, 0, 2) == 'VX') {

            foreach ($vals as $v) {
                if ( (strcmp($v, ":BL") == 0) ||
                     (strcmp($v, ":BATTLOAD") == 0) ) {
                    continue;
                } else if ($v == 0) {
                    continue;
                } else {
                    $volts[] = $v;
                }
            }
            $vals = $volts;
        }
        //$log->debug("Parsed values for: " . $tsid . " -> " . implode(', ', $vals));
        $newdata[$tsid] = $vals;
    }
    return $newdata;
};

