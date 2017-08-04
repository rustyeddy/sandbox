<?php

namespace SierraHydrog;

/**
 * User: rusty
 * Date: 9/27/15
 * Time: 9:21 AM
 */
class Site
{
    public $_id;

    // Set everything to public we want exposed.
    public $mnemonic;
    public $siteName;

    // All these values from the PDT
    public $projectId = "";
    public $nesdisId = "";

    // Optional
    public $pdtType = "";
    public $decodingScheme = "Oldest First";
    public $scanInterval = 15;
    public $shefOrder = "";

    // What parser to use?
    private $parser = null;

    // The AQUARIUS timeseriesIds
    public $timeseriesIds = [];

    public $latitude = "";
    public $longitude = "";
    public $elevation = "";
    public $specialRemarks = "";

    public $notes = "";

    /** ------------------------ NOT PERSISTED IN SiteID Collection ---------- **/

    // This comes from AQUARIUS LocationID
    public $locationId = 0;

    // Shef keys are the exploded version of shefOrder
    private $shefKeys;

    // This is to track the files we have parsed so far.
    private $dataFiles = array();

    // Cache timeseries strings
    private $timeseries = null;

    // Hold on to problem files after processing
    private $problemFiles = array();

    // Hold on to properly processed files for archiving
    private $processedFiles = array();

    // Missing timeseries
    private $missingTimeseries = null;

    /**
     * A constructor will nothing to do.
     */
    public function __construct() { }

    /**
     * @return String the site Mnemonic (e.g. NFW)
     */
    public function getMnemonic() {
        return $this->mnemonic;
    }

    /**
     * @return string the NESDIS ID.
     */
    public function getNesdisId() {
        return $this->nesdisId;
    }

    /**
     * @return string the SHEF order.
     */
    public function getShefOrder() {
        return $this->shefOrder;
    }

    /**
     * Get the SHEF keys for this sites measurements.  We'll have to bust 'em
     * out based on their order.
     */
    public function getShefKeys() {

        // Do this once
        if ( $this->shefKeys === null ) {
            $this->shefKeys = explode( ", ", $this->shefOrder );
            array_unshift( $this->shefKeys, "Title" );
        }
        return $this->shefKeys;
    }

    public function getTimeseriesIds()
    {
        return $this->timeseriesIds;
    }

    /**
     * Set the parser we'll be using for this Site
     *
     * @param $parser
     */
    public function setParser( $parser ) {
        global $log;

        if ( $this->parser != null ) {
            $log->warn($this->mnemonic . " setting a new parser, even though we already had one");
        }

        $log->debug("Setting parser " . get_class($parser));
        $this->parser = $parser;
    }

    /**
     * Pass an array decoded from a single site instance from the sites.json file.
     *
     * @param $sarry a decoded array from sites.json
     */
    function fromArray( $sarry ) {
        global $log;

        foreach ( $sarry as $k => $v ) {
            $ref = new \ReflectionObject($this);
            if ( ! $ref->hasProperty($k) ) {
                $log->error("Unknown from array property: $k\n");
                exit;
            }
            if ($k == "timeseriesIds") {
                $this->$k = $v->getArrayCopy();
            } else {
                $this->$k = $v;
            }
        }
        return $this;
    }

    /**
     * getTimeseries()
     *
     * Foreach $shef key, lookup the corresponding SHEF description
     * then formulate the TimeseriesString of the format:
     * "Long Description".GOES@SiteID.
     *
     * Then query AQUARIUS to determine if these timeseries have
     * been created in AQUARIUS.  If the Timeseries has been created
     * save the timeseries ID from Aquarius.
     *
     * @return array - returns an array of timeseries strings.
     */
    public function getTimeseries() {
        global $log;

        if ($this->timeseries === null) {
            $tm = TimeseriesManager::getInstance();
            $tslist = $tm->getDescriptionsForLocation($this->getMnemonic());
            if (!$tslist) {
                return false;
            }
            $this->timeseries = $tslist[$this->mnemonic];
        }
        return $this->timeseries;
    }

    /**
     * A list of Timeseries that do not yet exist in AQUARIUS
     *
     * @return the list of timerseries IDs that need to be created.
     */
    public function getMissingTimeseries() {
        global $log;

        $tsarray = $this->getTimeseries();
        if (! $tsarray) {
            $log->error("Could not retrieve the TimeSeries array");
            return false;
        }

        $smap = SHEFMap::getInstance()->getShefMap();
        foreach ($this->getShefKeys() as $k) {
            if ($k == "title" || $k == "Title") continue;

            $descr = "";
            if (array_key_exists($k, $smap)) {
                $descr = $smap[$k]['long'];
            } else {
                print "I don't know the SHEF code: $k\n";
                exit(-3);
            }

            $tstr = sprintf("%s.%s@%s", $descr, "GOES", $this->getMnemonic());
            if (!$tsarray || !array_key_exists($tstr, $tsarray)) {
                $this->missingTimeseries[] = $tstr;
            }
        }

        return $this->missingTimeseries;
    }

    /**
     * dataFiles - add and/or retrieve files to be processed.
     *
     * @param $files optional array of files.
     * @return the array of files or null.
     */
    public function dataFiles($files = null) {
        if (null !== $files) {
            $this->dataFiles = array_merge( $this->dataFiles, $files );
        }
        return $this->dataFiles;
    }

    /**
     * Get the location ID from AQUARIUS or the cache if it exists.
     * @return bool|int
     */
    public function getLocationId() {
        global $log;

        $aq = AquariusSoap::getInstance();

        if ($this->locationID <= 0) {
            $this->locationId = $aq->getLocationId($this->mnemonic);
            if ($this->locationId == false || $this->locationId <= 0) {
                $this->locationId = -1;
                $log->error("Failed to get the location ID");
                return false;
            }
        }
        return $this->locationId;
    }

    /**
     * @return string a human readable string.
     */
    public function toString() {
        $str = join( " ", [
            $this->mnemonic, $this->siteName, $this->projectId, $this->nesdisId
        ] );
        return $str;
    }

    /**
     * @return a network list string separated by commas.
     */
    public function toNetworkList() {
        $str = join( ", ", [
            $this->nesdisId, $this->mnemonic, $this->siteName
        ]);
        return $str;
    }

    /**
     * Describe this site.
     */
    public function describe()
    {
        $ref = new \ReflectionClass($this);
        $props = [];
        foreach($ref->getProperties() as $prop) {
            $name = $prop->name;
            $props[$name] = $this->$name;
        }
        $json = json_encode($props, JSON_PRETTY_PRINT);
        return $json;
    }
}
