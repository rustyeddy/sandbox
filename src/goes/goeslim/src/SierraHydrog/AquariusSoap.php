<?php

namespace SierraHydrog;

class AquariusSoap {

    use ContainerTrait;

	// This is a singleton
	private static $instance = null;

	// Our soap handle.
	private $soap = null;

    private $authToken = null;

	// Single get an instance
	static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new AquariusSoap();
            static::$instance->authenticate();
		}
		return static::$instance;
	}

	// Prevent another instance of being created
	protected function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

    /**
     * @return Aquarius soap handle.  Create it if it does not
     * already exist.
     */
    public function getSoap() {
        $log = $this->logger();

        $aq = AquariusSoap::getInstance();
        $soap = $aq->soap;

        if ($soap === null ||
            $soap->isConnectionValid() == false ) {
            $this->soap = null;
            $this->authenticate();
        }

        if ( $this->soap->isConnectionValid() == false ) {
            $log->error("We can not seem to get a good soap handle");
            die("Error getting SOAP handle\n");
        }
        return $this->soap;
    }

	/**
	 * Authenticate with Aquarius SOAP service.
	 */
	public function authenticate() {
        $log = $this->logger();
		$config = $this->settings();

		$soapurl = "http://" . $config['aquarius']['host'] . 
                 "/AQUARIUS/AQAcquisitionService.svc";
		$wsdlurl = $soapurl . "?WSDL";

        try {
            $s = new \SoapClient( $wsdlurl, array( "trace" => true ) );
        } catch (\SoapFault $sf) {
            $msg = $sf->getMessage();
            $log->error($msg);
            die($msg . "\n");
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $log->error($msg);
            die($msg . "\n");
        }

		$u = $config['aquarius']['user'];
		$p = $config['aquarius']['pass'];

		$params = array('user' => $u, 'encodedPassword' => $p);
		$res = $s->GetAuthToken($params);
        $this->authToken = $res->GetAuthTokenResult;

		$this->soap = $s;
		return $s;
	}

    /* ------------------------------- Locations ---------------------------- */

    /**
     * Get all the locations from Aquarius.
     *
     * @param $params - null
     * @return array  - An array of location DTOs
     */
    public function getAllLocations( ) {
        $log = $this->logger();

        $soap = $this->getSoap();
        assert($soap);

        $res = null;
        try {
            $res = $soap->GetAllLocations( array() );
        } catch ( Exception $e ) {
            $msg = "Unable to to get All locations: " . $e->getMessage();
            $log->warn( $msg );
            die($msg . "\n");
        }

        $locations = $res->GetAllLocationsResult->LocationDTO;
        return $locations;
    }

    public function getLocation( $locstr )
    {
        $log = $this->logger();

        $lid = false;
        if ( is_string($locstr) ) {
            $lid = $this->getLocationId($locstr);
        } else if (is_long($locstr)) {
            $lid = $locstr;
        } else {
            $log->error("Don't know how to get location with: $locstr");
        }

        $soap = $this->getSoap();
        assert($soap);

        try {
            $res = $soap->GetLocation( ['locationId' => $lid] );
        } catch (\SoapFault $sf) {
            $msg = $sf->getMessage();
            $log->fatal($msg);
            die($msg . "\n");
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $log->fatal($msg);
            die($msg . "\n");
        }

        $loc = $res->GetLocationResult;
        return $loc;
    }

    /**
     * @param $siteId The string representation of the locationIdentifier
     * @return the (long) representation of the locationId indexed by AQUARIUS
     */
    public function getLocationId( $siteId ) {
        $log = $this->logger();

        $soap = $this->getSoap();
        if ($soap == false) {
            // we are screwed
            return false;
        }

        $res = $soap->GetLocationId( array('locationIdentifier' => $siteId ) );
        if ($res == false) {
            $log->error("Could not retrieve the LocationId for " . $siteId);
            return false;
        }
        $locId = $res->GetLocationIdResult;
        if ($locId <= 0) {
            $log->debug("Problem with the result from GetLocationId for " . $siteId);
            return false;
        }

        return $locId;
    }

    /**
     * @param $params
     * @return null
     */
    public function createLocation($params)
    {
        $log = $this->logger();
        $soap = $this->getSoap();

        try {
            $res = $soap->CreateLocation( ['location' => $params] );
        } catch (\SoapFault $f) {
            $log->error($f->getMessage());
            $log->debug($f->getTraceAsString());
            return null;
        }

        $locId = $res->CreateLocationResult;
        $log->debug("Created location: " . $locId);
        return $locId;
    }

    /* ------------------------------- Timeseries ---------------------------- */

    /**
     * Try to retrieve the timeseries ID (long) for the timeseries string.
     *
     * @param $tsstr the string for time series: "shef descriptions".GOES@StationID
     * @return int  The timeseries ID if > 0, 0 if unknown, < 0 if a problem with soap call
     */
    public function getTimeseriesId($tstr) {
        $log = $this->logger();

        $soap = $this->getSoap();

        $log->debug("Get timeseries ID for: " . $tstr);
        $res = $soap->GetTimeSeriesID( array('identifier' => $tstr ) );
        if ( $res === false ) {
            $log->error("Error retrieving the time series id for " . $tstr);
            return -1;
        }

        $tid = $res->GetTimeSeriesIDResult;
        if ( $tid <= 0 ) {
            $log->debug("Could not find a timeseries for " . $tstr);
        }

        $log->debug("Timeseries id for " . $tstr . " is " . $tid);
        return $tid;
    }

    /**
     * Get the timeseries for the given Location ID.
     *
     * @param $locId Location ID as kept by AQUARIUS
     * @return The timeseries list or null if it does not exist.
     */
    public function getTimeseriesListForLocation($locId)
    {
        $log = $this->logger();
        $log->debug("Getting time series for Location ID: " . $locId);

        $soap = $this->getSoap();
        try {
            $res = $soap->GetTimeSeriesListForLocation(array('locationID' => $locId ));
        } catch (\SoapFault $sf) {
            $log->error($sf->getMessage());
            return null;
        }

        $foo = $res->GetTimeSeriesListForLocationResult;
        if (!property_exists($foo, 'TimeSeriesDescription')) {
            $log->warn("No timeseries for LocId: " . $locId);
            return null;
        }

        $tslist = $foo->TimeSeriesDescription;
        return $tslist;
    }

    /**
     * Store this particular value in the corresponding TimeSeries.  If the
     * timeseries does not exist, we need to make a record of it then have
     * it created.
     *
     * @param $tsid  "SHEF Description".GOES@StationId
     * @param $csvstr yyyy-mm-dd HH:MM:ss, xx.xx,,,,
     * @param $username user that inserted this value
     */
    public function appendTimeseries($tsid, $csvstr, $username = '')
    {
        $config = $this->settings();
        $log = $this->logger();
        $cache = $this->cache();

        if ($username == null) {
            $username = $config['username'];
        }

        $params = array(
            'id' 		=> $tsid,
            'csvbytes'	=> $csvstr,
            'userName'	=> $username,
        );

        // We'll need to verify the timeseries exists first, most likely.
        $soap = $this->getSoap();

        /*
         * TODO: Fix when authentication fails...
         */
        $failure = false;
        try {
            $res = $soap->AppendTimeSeriesFromBytes2( $params );
            if ($res->AppendTimeSeriesFromBytes2Result->NumPointsAppended > 0) {
                $cache->incr("aquarius-saved");
                $log->debug("Saved a value in aquarius: $tsid");
            }
            print_r($res);

        } catch (\SoapFault $sf) {
            $log->error($sf->getMessage() . " " . $csvstr);
            $failure = true;
        } catch (\Exception $e) {
            $log->error($e->getMessage());
            $failure = true;
        }

        if ($failure) {
            $log->error("Failed to store : $tsid -> $csvstr ");
            $cache->lpush("aquarius-failed", $tsid . " " . $csvstr);
        }

        $results = null;
        if ($res && property_exists($res, 'AppendTimeSeriesFromBytes2Result')) {
            $results = $res->AppendTimeSeriesFromBytes2Result;
            $results->data = $csvstr;
        }

        return $results;
    }

    /**
     * @param $params
     * @return bool
     */
    public function createTimeseries( $params )
    {
        $log = $this->logger();

        $soap = $this->getSoap();

        $res = null;
        try {
            $res = $soap->CreateTimeSeries2( $params );
        } catch (\SoapFault $sf) {
            $log->error($sf->getMessage());
            return false;
        } catch (\Exception $e) {
            $log->error($e->getMessage());
            return false;
        }
        if ( $res == false ) {
            $log->error("Problem creating the timeseries for " . $params['param']);
            return false;
        }
        $tsid = $res->CreateTimeSeries2Result;
        return $tsid;
    }

    /**
     * @param $tid
     * @return bool|null
     */
    public function deleteTimeseries($tid)
    {
        $log = $this->logger();

        $soap = $this->getSoap();
        $res = null;

        try {
            $res = $soap->DeleteTimeSeries( ['timeSeriesId' => $tid] );
        } catch (\SoapFault $sf) {
            $log->error($sf->getMessage());
            return false;
        } catch (\Exception $e) {
            $log->error($e->getMessage());
            return false;
        }
        if ( $res == false ) {
            $log->error("Problem deleting time series" . $tid);
            return false;
        }
        return $res;
    }
}
