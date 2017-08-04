<?php

namespace SierraHydrog;


class Timeseries
{
    private $timeseriesString = null;
    private $shef;
    private $description;
    private $label = "GOES";
    private $siteId;
    private $aquariusId;

    /***
     * Timeseries constructor.
     *
     * @param string $shef optional SHEF code.
     * @param string $siteId optional SiteID
     */
    public function __construct($shef = null, $siteId = null)
    {
        if ($shef != null) {
            $this->shef = $shef;
        }

        if ($siteId != null) {
            $this->siteId = $siteId;
        }
    }

    /**
     * Get the long description of the SHEF code for this timeseries.
     *
     * @return string the long description of false if something went wrong.
     */
    public function getDescription() {
        global $log;

        // If description is null we must get it from the SHEF map
        if ($this->description === null) {

            // We have a problem if we don't have a SHEF code.
            if ( $this->shef === null ) {
                $log->error( "Error SHEF code not set can't get description" );
                return false;
            }

            // The shefMap to get the long description from the short code
            $shefMap = SHEFMap::getInstance();
            $this->description = $shefMap->getDescription( $this->shef );
            if ($this->description === false or $this->description == "") {
                $log->error("could not get description from SHEF code: " . $this->shef);
                return false;
            }
        }

        // Return the description
        return $this->description;
    }

    /**
     * @return string. Return the timeseries string.
     */
    public function getTimeseriesString() {
        global $log;

        if ($this->shef == null || $this->siteId == null) {
            $log->error("SHEF and SiteId must be defined to create the timeseries string");
            return false;
        }

        if (null === $this->timeseriesString) {
            $this->timeseriesString = sprintf("%s.%s@%s", 
                                              $this->getDescription(), 
                                              $this->label,
                                              $this->siteId );
        }
        return $this->timeseriesString;
    }

    /**
     * Get the TimeseriesID from AQUARIUS can be used later for appending
     * to the timeseries.
     *
     * @return long AQUARIUS Timeseries ID if <= 0 the Timeseries does not exist in AQUAURIUS.
     */
    public function getAquariusId() {

        // Get read to talk to AQUARIUS
        $aq = AquariusSoap::getInstance();

        $ts = $this->getTimeseriesString();

        /*
         * Ask AQUARIUS for the ID.
         * TODO: wrap in a try catch.
         */
        $result = $aq->getTimeseriesId($ts);
        return $result;
    }

    /**
     * @param $ts
     * @param $params
     * @return mixed
     */
    public function getTimeseriesData($ts, $params)
    {
        $aq = AquariusRest::getInstance();
        $results = $aq->getTimeseriesData($ts, $params = null);

        return $results;
    }
}