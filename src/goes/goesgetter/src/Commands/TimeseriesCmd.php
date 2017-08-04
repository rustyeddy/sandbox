<?php

namespace SierraHydrog\Goes\Commands;


class TimeseriesCmd extends CommandBase
{
    public function __construct()
    {
        $this->command_name = "timeseries";
        $this->helpstr = "Interact with AQUARIUS timeseries";

        $this->commands = [
            'append' => [
                'usage' => 'goes timeseries append {tstr} {data}',
                'help'  => 'append data to a timeseries',
                'proc'  => 'append',
            ],
            'create' => [
                'usage' => 'goes timeseries create {tstr}',
                'help'  => 'create a timeseries string',
                'proc'  => 'create',
            ],
            'create-missing' => [
                'usage' => 'goes timeseries create-missing',
                'help'  => 'create all missing timeseries string',
                'proc'  => 'createMissingTimeseries',
            ],

            'data' => [
                'usage' => 'goes timeseries data {tstr}',
                'help'  => 'get timeseries raw data',
                'proc'  => 'getData',
            ],
            'delete' => [
                'usage' => 'goes timeseries delete {tstr}',
                'help'  => 'delete a timeseries string',
                'proc'  => 'delete',
            ],
            'describe' => [
                'usage' => 'goes timeseries describe {tstr}',
                'help'  => 'Describe the AQUARIUS timeseries for tstr',
                'proc'  => 'describe',
            ],
            'id' => [
                'usage' => 'goes timeseries id {tstr}',
                'help'  => 'Get the AQUARIUS ID for the timeseries',
                'proc'  => 'getId',
            ],
            'list' => [
                'usage' => 'goes timeseries list {tstr}',
                'help'  => 'Get a list of AQUARIUS timeseries',
                'proc'  => 'listAll',
            ],
            'location' => [
                'usage' => 'goes timeseries location {locId}',
                'help'  => 'Get a list of AQUARIUS timeseries for location',
                'proc'  => 'getLocationTimeseries',
            ],
            'params' => [
                'usage' => 'goes timeseries params {paramId}',
                'help'  => 'list parameters known by AQUARIUS',
                'proc'  => 'getParams',
            ],
        ];
    }

    /**
     * @param null $args
     * @return null
     */
    public function append($args, $params = null)
    {
        global $log;

        if ($args == null && count($args) < 2) {
            $log->info("Need more arguments to append data to this Timeseries");
            return false;
        }

        $tstr = array_shift($args);
        $data = array_shift($args);

        $tm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $res = $tm->appendTimeseries($tstr, $data);
        if ($res) {
            $json = json_encode($res);
        }
        return $json;
    }

    /**
     * Get all timeseries and list them out.
     */
    public function listAll($args = null)
    {
        global $config;

        $tsm = \SierraHydrog\Goes\TimeseriesManager::getInstance();

        $desc = $tsm->getTimeseriesDescriptions();
        if ($desc == null) {
            return false;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                foreach ($desc as $ts => $d) {
                    $output .= "$ts: \n";
                    foreach($d as $t) {
                        $output .= sprintf("%40s : %s\n", $t->Identifier, $t->Unit);
                    }
                }
                break;

            case 'json':
                $output = json_encode($desc);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    /**
     * Describe the Timeseries as represented by the ID
     *
     * @param $args
     */
    public function describe($args, $params = nulll)
    {
        global $log;
        global $config;

        if (count($args) < 1) {
            $log->warn($this->usage());
            return false;
        }
        $tstr = array_shift($args);

        $tsm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $ts = $tsm->getTimeseriesDescription($tstr);
        if ($ts == false || $ts == null) {
            $log->warn("No data for timeseries " . $tstr);
            return false;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                foreach($ts as $k => $v) {
                    if (!is_array($v)) {
                        if (!strncmp($k, "Computation", strlen("Computation")) ) continue;
                        if (!strcmp($k, "SubLocationIdentifier")) continue;
                        $output .= sprintf( "%20s: %-s\n", $k, $v );
                    }
                }
                break;

            case 'json':
                $output = json_encode($ts);
                break;

            case 'html':
                // TODO: get the html
                break;
        }
        return $output;
    }

    /**
     * DELETE a timeseries
     *
     * @param $args
     * @param null $param
     * @return bool|string
     */
    public function delete($args, $param = null)
    {
        global $log;
        global $config;

        if (count($args) < 1) {
            $log->warn("You'll need to supply a timeseries to delete one");
            return false;
        }

        $tstr = array_shift($args);
        $aq = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $res = $aq->deleteTimeseries($tstr);

        $output = ($res === false) ? "failed" : "success";
        switch ($config['output']) {
            case 'text':
                break;
            case 'json':
                $output = json_encode(['result' => $output]);
                break;
            case "html":
                // TODO
                break;
        }
        return $output;
    }

    private function parseTimestr( $str ) {

        $foo = explode('.', $str);
        $bar = explode('T', $foo[0]);

        $d = $bar[0];
        $t = $bar[1];
        $dt = $d . " " . $t;
        return $dt;
    }


    /**
     * INCOMPLETE.
     *
     * @param $args
     * @param null $params
     * @return bool|string
     */
    public function getData($args, $params = null)
    {
        global $config;
        global $log;

        if (count($args) < 1) {
            $log->warn("Must supply a timeseries string to get an ID ");
            return false;
        }

        $tstr = array_shift($args);
        $tm = \SierraHydrog\Goes\TimeseriesManager::getInstance();

        $res = $tm->getTimeseriesData($tstr, $params);
        if ($res === false) {
            $log->error("Failed to get timeseries data for: " . $tstr);
            return false;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                $foo = json_decode($res);
                $from = $this->parseTimestr($foo->TimeRange->StartTime);
                $to = $this->parseTimestr($foo->TimeRange->EndTime);

                $output .= "\nData points for $tstr:\n";
                $output .= "\t range from: " . $from;
                $output .= "\n\t         to: " . $to;
                $output .= "\n\n";
                foreach( $foo->Points as $p ) {
                    $timestamp = $this->parseTimestr($p->Timestamp);
                    $output .= sprintf("%30s: %-20s\n", $timestamp, $p->Value);
                }
                break;

            case 'json':
                $output = $res;
                break;

            case "html":
                // TODO
                break;
        }
        return $output;
    }

    /**
     * Get the Timeseries ID for the given timeseries string.
     *
     * @param $args the timeseries to get an ID for.
     * @return mixed The ID of the timeseries.
     */
    public function getId($args)
    {
        global $log;
        global $config;

        if (count($args) < 1) {
            $log->warn("Must supply a timeseries string to get an ID ");
            return false;
        }

        $tstr = array_shift($args);

        $tsm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $id = $tsm->getTimeseriesId($tstr);

        $output = "";
        switch ($config['output']) {
            case 'text':
                $output = $tstr . " " . $id;
                break;
            case 'json':
                $output = json_encode([ $tstr => $id]);
                break;
            case "html":
                // TODO
                break;
        }
        return $output;
    }

    /**
     * @param $args
     * @return string
     */
    public function getLocationTimeseries($args)
    {
        global $config;
        global $log;

        if (count($args) < 1) {
            $log->warn("need to supply a location to get the timeseries");
            return false;
        }
        $loc = array_shift($args);

        $tsm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $tsList = $tsm->getDescriptionsForLocation($loc);
        if ($tsList == null) {
            $log->warn("No timeseries descriptions for $loc");
            return false;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                foreach ($tsList as $site => $list) {
                    $output .= "Site: $site\n";
                    foreach ($list as $l) {
                        $output .= sprintf("%40s %8d %20s %d\n",
                            $l->TimeSeriesIdentifier,
                            $l->AqDataID, $l->EndTime, $l->TotalSamples);
                    }
                }
                break;

            case 'json':
                $output = json_encode($tsList);
                break;
        }
        return $output;
    }

    /**
     * @param $args timseries strings to create.
     */
    public function create($args, $params = null)
    {
        global $config;
        global $log;

        if (count($args) < 1) {
            $log->warn("Not enough arguments to create a timeseries");
            return false;
        } elseif ($args[0] == "help") {
            $output = "goes timeseries create Sensor.ID@Station";
            return $output;
        }

        $units = array_key_exists('units', $params) ?
            $params['units'] : null;

        if ($units == null) {
            $output = "Must supply units when creating timestring ";
            $log->error($output);
            return $output;
        }

        $tsids = [];
        $tm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        foreach ($args as $arg) {
            $tsid = $tm->createTimeseries($arg, $units);
            $tsids[$arg] = $tsid;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                foreach ($tsids as $tsid => $id) {
                    $output .= sprintf("%20s => %d", $tsid, $id);
                }
                break;

            case 'json':
                $output = json_encode($tsids);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    public function createMissingTimeseries($args, $params = null)
    {
        global $config;
        global $log;

        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        $sites = $sm->getSites();

        $tm = \SierraHydrog\Goes\TimeseriesManager::getInstance();

        foreach ($sites as $site) {
            foreach ($site->timeseriesIds as $tstr) {
                $tid = $tm->getTimeseriesId($tstr);
                if ($tid === false || $tid <= 0) {
                    print "Creating timeseries: " . $tstr . "\n";
                    $tid = $tm->createTimeseries($tstr);
                } else {
                    print "Time series " . $tstr . " alread exists tid: " . $tid . "\n";
                }
            }
        }
    }

    /**
     * Get a list of parameters from AQUARIUS.
     *
     * @param $args
     * @param null $params
     * @return bool|string
     */
    public function getParams($args, $params = null)
    {
        global $config;
        global $log;

        $sm = \SierraHydrog\Goes\SHEFMap::getInstance();
        $aqp = $sm->getAquariusParams();
        if (!$aqp) {
            $log->error("Could not retrieve AQAURIS parameters");
            return false;
        }

        $params = [];
        if (count($args)) {
            foreach ($args as $arg) {
                if (!array_key_exists($arg, $aqp)) {
                    $params[$arg] = "does not exist";
                    continue;
                }
                $v = $aqp[$arg];
            }
        } else {
            $params = $aqp;
        }

        $output = "";
        switch ($config['output']) {
            case 'text':
                foreach($params as $p) {
                    $output .= sprintf("%s, %s, %s, %s\n", $p->Identifier, $p->DisplayName,
                        $p->UnitGroupIdentifier, $p->UnitIdentifier);
                }
                break;

            case 'json':
                $output = json_encode($params);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;

    }
}
