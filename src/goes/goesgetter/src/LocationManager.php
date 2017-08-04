<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 12/30/15
 * Time: 4:41 PM
 */

namespace SierraHydrog\Goes;


class LocationManager
{
    private static $instance = null;

    private $locations = null;

    private function __construct() {}

    public static function getInstance() {
        if (LocationManager::$instance == null) {
            LocationManager::$instance = new LocationManager();
        }
        return LocationManager::$instance;
    }

    /**
     * @return a list of all locations.
     */
    public function getLocations()
    {
        global $log;

        if ($this->locations == null) {

            $aq = AquariusSoap::getInstance();
            if ( $aq == null ) {
                $log->error( "Could not get an instance of Aquarius" );
                return null;
            }

            $locs = $aq->getAllLocations();
            foreach ( $locs as $l ) {
                $lid = $l->Identifier;
                $this->locations[ $lid ] = $l;
            }
        }
        return $this->locations;
    }

    /**
     * @param $locstr
     * @return null
     */
    public function getLocationByName($locStr)
    {
        $locId = false;

        if ( $this->locations != null && count( $this->locations ) > 0 ) {

            if ( array_key_exists( $locStr, $this->locations ) ) {
                $loc = $this->locations[ $locStr ];
                return $loc;
            }
        }

        $aq = AquariusSoap::getInstance();
        $loc = $aq->getLocation($locStr);

        $this->locations[$locStr] = $loc;
        return $loc;
    }

    /**
     * @param $locstr: the human location identifier
     * @return AQUARUIUS location ID
     */
    public function getLocationId($locStr)
    {
        $loc = $this->getLocationByName($locStr);
        if ($loc) {
            return $loc->LocationId;
        }

        $aq = AquariusSoap::getInstance();
        $locId = $aq->getLocationId($locStr);
        return $locId;
    }

    /**
     * Create a location.
     */
    public function createLocation($params)
    {
        global $log;

        $must = [
            'LocationName',
            'Identifier',
            'LocationType',
            'UtcOffset',
            'LocationPath',
        ];

        $maybe = [
            'Longitude',
            'Latitude',
            'Elevation',
            'ElevationUnits'
        ];

        $keys = array_keys($params);

        // Verify we have all the mandatory arguments
        if (count(array_intersect($must, $keys)) != count($must)) {
            $log->error("Missing mandatory keys to create the location");
            $log->debug("\tMandatory keys are: " . implode(',', $must));
            return null;
        }

        $notok = array_diff($keys, $must, $maybe);
        if (count($notok)) {
            $log->warn("Hmmm we have some keys that are not OK: ",
                implode(", ", $notok));
        }

        $aq = AquariusSoap::getInstance();
        $locId = $aq->createLocation($params);
        $log->debug("Created location ID: " . $locId);
        return $locId;
    }

    /**
     * Clear the cache
     */
    public function clearAll()
    {
        $this->locations = null;
    }

    /**
     * @return list of all locations
     */
    public function getAll()
    {
        return $this->getLocations();
    }
}
