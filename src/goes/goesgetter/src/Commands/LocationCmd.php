<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 12/29/15
 * Time: 6:21 PM
 */

namespace SierraHydrog\Goes\Commands;
use SierraHydrog\Goes\LocationManager;

class LocationCmd extends CommandBase
{
    public function __construct()
    {
        $this->command_name = "location";
        $this->helpstr = "Interact with AQUARIUS locations";

        $this->commands = [
            'list' => [
                'usage' => 'goes location list',
                'help'  => 'list out the set of AQUARIUS locations',
                'proc'  => 'listAll',
            ],
            'describe' => [
                'usage' => 'goes location describe <siteid>',
                'help'  => 'describe the location represented by siteid',
                'proc'  => 'describe',
            ],
            'id' => [
                'usage' => 'goes location id <siteId>',
                'help'  => 'Get the AQUARIUS ID for the location',
                'proc'  => 'getLocationId',
            ],
        ];
    }

    /**
     * list all of the locations known to AQUARIUS
     */
    public function listAll($args, $params = null)
    {
        global $config;

        $lm = LocationManager::getInstance();
        $locs = $lm->getLocations();

        $output = [];
        switch ($config['output']) {
            case 'text':
                $output[] = sprintf("%8s: %-7s -> %s", "LocStr", "LocId",
                    "Location Name");

                foreach ($locs as $l) {
                    $output[] = sprintf("%8s: %-7s -> %s", $l->Identifier,
                        $l->LocationId, $l->LocationName);
                }
                $output = implode("\n", $output);
                break;

            case 'json':
                $output = json_encode($locs);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    /**
     * Describe a specific location.
     *
     * @param $args: pull the location string
     * @return location associative array
     */
    public function describe($args, $params = null)
    {
        global $config;
        global $log;

        $locstr = array_shift($args);

        $lm = LocationManager::getInstance();
        $loc = $lm->getLocationByName($locstr);
        if (! $loc) {
            $log->warn("Could not retrieve AQUARIUS location info for: " .
                $loc);
            return false;;
        }

        $output = [];
        switch ($config['output']) {
            case 'text':
                foreach ($loc as $k => $v) {
                    $output[] = sprintf("%20s: %-s", $k, $v);
                }
                $output = implode("\n", $output);
                break;

            case 'json':
                $output = json_encode($loc);
                break;

            case 'html':
                // TODO
                break;
        }

        return $output;
    }

    /**
     * Get the location ID.
     *
     * @param $locstr location String
     * @return long   LocationID
     */
    public function getLocationId($args)
    {
        global $config;
        global $log;

        $locstr = array_shift($args);

        $lm = LocationManager::getInstance();
        $id = $lm->getLocationId($locstr);
        if ($id == null) {
            $log->error("Could not find AQUARIUS ID for location: " . $locstr);
            return false;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                $output = $locstr . ": " . $id;
                break;

            case 'json':
                $output = json_encode([$locstr => $id]);
                break;

            case 'html':
                // TODO
                break;
        }

        return $output;
    }

    /**
     * @param $args
     * @return null|string
     */
    public function create($args, $params = null)
    {
        global $config;

        if (count($args) < 3) {
            $this->output("Need at least 3 arguments for create a location");
            return null;
        }

        $lm = LocationManager::getInstance();
        $locId = $lm->createLocation($params);
        if ($locId == null) {
            $this->output("Could not create location\n");
            return false;
        }

        $output = json_encode(['locationId' => $locId]);
        return $output;
    }
}
