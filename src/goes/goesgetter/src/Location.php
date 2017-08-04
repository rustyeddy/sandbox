<?php

namespace SierraHydrog\Goes;

class Location {  

    // Array Members
    public $LocationId;
    public $Identifier;
    public $LocationName;
    public $Longitude;
    public $Latitude;
    public $UtcOffset;
    public $LocationPath;
    public $LocationTypeName;
    public $ExtendedAttributes;
    public $Elevation;
    public $ElevationUnits;

    // Classes queried from the Aquarius server
	private $timeSeries;

    /**
     * Create a location from an array.
     *
     * @param null $args
     */
	public function Location($args = null) {
        if ($args != null) {
    		foreach($args as $arg => $val) {
                $this->$arg = $val;
    		} 
        }
	}

    /**
     * Get this locationID
     *
     * @return mixed
     */
    function getLocationId() {
        return $this->LocationId;
    }

    // Get Field Visits
    function getFieldVisits() {
        $params = array( "locationId" => $this->LocationId );
        $visits = $this->soapCall( 'GetFieldVisitsByLocation', $params );
        if ($visits == null) {
            echo "Could not get field visits\n";
            return null;
        }

        $vs = $visits->GetFieldVisitsByLocationResult->FieldVisit;
        foreach ($vs as $v) {
            $visit = new FieldVisit($v);
            $this->fieldVisits[] = $visit;
        }
        return $this->fieldVisits;
    }

    // We need to get the time series for this particular location
    function getTimeSeriesListForLocation( ) {

        $params = array("locationId" => $this->LocationId);
        $ts = $this->soapCall( 'GetTimeSeriesListForLocation', $params );
        return $ts;
    }

    // Add a new location into Aquarius
    function createLocation() {
        $this->LocationId = null;
        if ($this->Identifier == null || $this->Identifier === "") {
            die ("FATAL: to create a location the identifier must NOT be null\n");
        }

        if ($this->LocationName == null || $this->LocationName === "") {
            die ("FATAL: to create a location the Location Name NOT be null\n");
        }

        /**
         * More sanity checks required..
         * <ul>
         *   <li>LocationPath</li>
         *   <li>Location Type</li>
         * </ul>
         */
        $params = array(
            //'LocationId' => null,
            'Identifier' => $this->Identifier,
            'LocationName' => $this->LocationName,
            //'LocationPath' => $this->LocationPath,
            'LocationTypeName' => $this->LocationTypeName,
            //'Longitute' => $this->Longitude,
            //'Latitude' => $this->Latitude,
            //'UtcOffset' => $this->UtcOffset,
            //'Elevation' => $this->Elevation,
            //'ElevationUnits' => $this->ElevationUnits,
        );

        //$res = $this->soapCall( 'CreateLocation', $params );

    }

    function getTimeSeriesDescriptionList() {
        
    }


    // Print this location
    function toString() {
        $str = sprintf( "%9s %8d   %s\n", $this->Identifier, $this->LocationId, $this->LocationName );
        return $str;        
    }
}