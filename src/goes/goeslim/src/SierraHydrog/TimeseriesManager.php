<?php

namespace SierraHydrog;

class TimeseriesManager
{
    use ContainerTrait;

    private $timeseriesDescriptions = null;
    private $missing = array();

    public function __construct() {
    }

    /**
     * @return null
     */
    public function getAll()
    {
        return $this->getTimeseriesDescriptions();
    }

    /**
     * Clear All cached values
     */
    public function clearAll()
    {
        $this->timeseriesDescriptions = [];
    }


    /**
     * Break out the timeseries string into it's individual
     * elements.
     *
     * @param $ts timeseries string
     * @return mixed array of timseries data
     */
    public function parseTimerseriesString($ts) {

        $parts = explode('.', $ts);
        $desc = $parts[0];
        $parts = explode('@', $parts[1]);
        $label = $parts[0];
        $sid = $parts[1];

        $tsdata['siteId'] = $sid;
        $tsdata['param'] = $desc;
        $tsdata['label'] = $label;

        return $tsdata;
    }

    /**
     * Get a cached version of this timeseries.
     */
    public function getTimeseriesFromFile()
    {
        $config = $this->settings();
        $log = $this->logger();

        $fname = $config['timeseries-file'];
        $log->debug("Getting timeseries data from file: " . $fname);
        $json = file_get_contents( $fname );
        if ($json == null) {
            return null;
        }
        return $json;
    }

    /**
     * Get descriptions of existing time series.
     */
    public function getTimeseriesDescriptions()
    {
        $config = $this->settings();
        $log = $this->logger();

        $siteManager = SiteManager::getInstance();
        assert($siteManager);

        if ($this->timeseriesDescriptions === null) {

            $aq = AquariusRest::getInstance();
            $tslist = $aq->getTimeSeriesDescriptionList();
            if (!$tslist) {
                return null;
            }

            $tsdesc = $tslist->TimeSeriesDescriptions;
            foreach ($tsdesc as $ts) {
                $lid = $ts->LocationIdentifier;
                $id = $ts->Identifier;
                $this->timeseriesDescriptions[$lid][$id] = $ts;
            }
        }
        return $this->timeseriesDescriptions;
    }

    /**
     * Get timeseries descriptions for a given location.
     *
     * NOTE: the returned structure from the SOAP call (used here) differ
     * with the REST call
     *
     * @param $loc
     * @return mixed
     */
    public function getDescriptionsForLocation($loc) {
        $log = $this->logger();

        $lm = LocationManager::getInstance();
        $lid = $lm->getLocationId($loc);
        if ($lid == null || $lid == 0) {
            $log->error("Looking for a non-existant location: $loc");
            return false;
        }

        $aq = AquariusSoap::getInstance();
        $timeseriesList = $aq->getTimeSeriesListForLocation($lid);
        if ($timeseriesList == null) {
            $log->error("No timeseries for location: $loc ID: $lid");
            return null;
        }

        if (is_array($timeseriesList)) {
            foreach ( $timeseriesList as $ts ) {
                $this->timeseriesDescriptions[ $loc ][ $ts->TimeSeriesIdentifier ] = $ts;
            }
        } else {
            $ts = $timeseriesList;
            $this->timeseriesDescriptions[$loc][ $ts->TimeSeriesIdentifier ] = $ts;
        }
        return $this->timeseriesDescriptions;
    }

    /**
     * @param $tstr
     * @return bool
     */
    public function getTimeseriesDescription($tstr)
    {
        global $log;

        $descr = $this->getTimeseriesDescriptions();
        $tsdata = $this->parseTimerseriesString($tstr);
        if (! $tsdata) {
            return false;
        }
        $sid = $tsdata['siteId'];
        $param = $tsdata['param'];

        $shef = \SierraHydrog\Goes\SHEFMap::getInstance();
        $map = $shef->getShefCode($tsdata['param']);
        if (!$map) {
            $log->warn("No mapping for param: ". $tsdata['param']);
            return false;
        }

        $aqdisplay = array_key_exists('aquarius-display', $map) ?
            $map['aquarius-display'] : null;

        $aqtsid = $aqdisplay . ".GOES@" . $sid;

        $details = false;
        if (array_key_exists($sid, $descr)) {
            $sensors = $descr[$sid];
            if (array_key_exists($tstr, $sensors)) {
                $details = $sensors[$tstr];
            } else if($aqdisplay && array_key_exists($aqtsid, $sensors)) {
                $details = $sensors[$aqtsid];
            } else {

                $log->warn("Looking for description of: " . $tstr .
                    "no record of station: " . $sid);
            }
        }
        return $details;
    }

    /**
     * Given the timeseries of the form param.label@station: determine
     * the timeseriesId (long) AQUARIUS uses as an index.
     *
     * TODO: store this in the corresponding Site object.
     *
     * @param $tstr
     * @return long timeseriesId used by AQUARIUS false or 0 if not found.
     */
    public function getTimeseriesId($tstr) {
        $log = $this->logger();
        $log->debug("Attempting to get timeseries id: " . $tstr);
        if (! $tstr || $tstr === "") {
            $log->error("We have a null timeseries str");
            return false;
        }
        $aq = AquariusSoap::getInstance();
        $res = $aq->getTimeseriesId( $tstr );
        if ($res == 0 ) {
            $log->debug("Timeseries does not exist for: " . $tstr);
        }
        return $res;
    }

    /**
     * TODO: this needs to be made better.
     *
     * @param $tsdata
     * @return int
     */
    public function getMeasurementUnit($tsdata)
    {
        $log = $this->logger();

        $siteId = $tsdata['siteId'];
        $param = $tsdata['param'];

        $shefmap = SHEFMap::getInstance();
        $map = $shefmap->getShefMap();

        $units = null;
        if (array_key_exists($param, $map)) {
            $shef = $map[$param];
            $units = $shef['units'];
        }

        return $units;
    }

    /**
     * Create a Timeseries from a timeseries string so we can insert data later.
     *
     * @param $ts the timeseries string.
     * @return mixed
     */
    public function createTimeseries($ts, $unit = null)
    {
        $log = $this->logger();

        $log->info("We are going to attempt to create: " . $ts);

        $tsdata = $this->parseTimerseriesString($ts);
        $siteId = $tsdata['siteId'];
        $param = $tsdata['param'];

        if (!$unit) {
            $unit = $this->getMeasurementUnit($tsdata);
        }
        if (!$unit) {
            $log->error("Can not determine unit of measurement");
            return null;
        }

        $lm = LocationManager::getInstance();
        $locid = $lm->getLocationId($siteId);

        $params = array(
            'parentId'				=> $locid,
            'label'					=> $tsdata['label'],
            'comment'				=> "",
            'description'			=> "Retrieved from GOES / LRGS",
            'parameter'				=> $param,
            'utcOffsetInMinutes'	=> 0,
            'unit'					=> $unit,
            'maxGap'				=> 0.0,
        );

        $aq = AquariusSoap::getInstance();
        $tsid = $aq->createTimeseries($params);

        $log->info("Attempt to create timeseries: " . $ts . " tsid " . $tsid);
        return $tsid;
    }


    /**
     * @param $tstr
     * @param $data
     * @param null $user
     * @return mixed
     */
    public function appendTimeseries($tstr, $data, $user = null)
    {
        $log = $this->logger();

        $appresult = [
            'tstr' => $tstr,
            'tid'  => 0,
            'numPointsAppended' => 0,
            'data' => $data,
            'appendToken' => null,
            'messages' => "",
        ];

        // TODO: Cache the $tid in Redis.
        $tid = $this->getTimeseriesId($tstr);
        if ($tid <= 0) {
            $log->error("Could not determine the timeseries ID for $tstr");
            $appresult['message'] = "Timeseries unknown by AQUARIUS for $tstr";
            return $appresult;
        }
        $appresult['tid'] = $tid;
        $aq = AquariusSoap::getInstance();
        $res = $aq->appendTimeseries($tid, $data, $user);
        if (!$res) {
            $appresult['message'] = "Unkown failure appending result";
            $log->error("Could not append timeseries");
            return false;
        }

        $appresult['appendToken'] = $res->AppendToken;
        $appresult['numPointsAppended'] = $res->NumPointsAppended;
        return $appresult;
    }

    /**
     * Save the append results to a file for later record keeping, diagnosis, etc.
     * The file is a CSV (actually a ';' separated) variable file, since the values
     * written to aquarius are a CSV string.
     *
     * @param $tstr
     * @param $data
     * @param $appendid
     */
    public function recordAppend($appendResult, $file)
    {
        $config = $this->settings();
        $log = $this->logger();

        $appendResult['written'] = \Carbon\Carbon::now();
        $appendResult['filename'] = $file->getFilename();

        $tsfile = $config['stored'];

        $harray = null;
        if (!file_exists($tsfile)) {
            $harray = array_keys($appendResult);
        }
        $fp = fopen($tsfile, "a+");
        if ($fp == false) {
            $log->error("Could not write timestring to file: " .
                sprintf("%s: %s appendid: %s", $tsfile));
            return false;
        }

        if ($harray) {
            $err = fputcsv($fp, $harray, ';');
            if ($err === false) {
                $log->fatal("Could not record the timestamp action");
                exit(-1);
            }
        }
        $valarray = array_values($appendResult);
        $err = fputcsv($fp, $valarray, ';');
        if ($err === false) {
            $log->fatal("Could not record the timestamp data");
            exit(-2);
        }
        return $appendResult;
    }

    /**
     * Save the timeseries
     *
     * @param $meas
     */
    public function storeTimeseries($meas)
    {
        $log = $this->logger();

        $csv = $this->getCsvString($meas);
        $tstr = $meas->tsid;
        $res = $this->appendTimeseries($tstr, $csv);
        $meas->result = $res;
        if ($res['tid'] == 0) {
            return $res;
        }
        return $res;
    }

    /**
     * @param $measurement
     *
     * @return CSV string for this measurement.
     */
    private function getCsvString($meas)
    {
        $log = $this->logger();
        $config = $this->settings();

        $csvstr = $meas->timestamp . ", " . $meas->value . ",,,,";
        return $csvstr;
    }

    /**
     * TODO: Move this to the TimeSeries Class
     *
     * @param $tstr
     */
    public function getTimeseriesData($tstr, $param)
    {
        $log = $this->logger();
        $aq = $this->aquarius();
        
        $res = $aq->getTimeseriesData($tstr, $param);
        return $res;
    }

    /**
     * @param $tstr
     * @return bool
     */
    public function deleteTimeseries($tstr)
    {
        $log = $this->logger();

        $this->timeseriesDescriptions = null;

        $tid = $this->getTimeseriesId($tstr);
        if ($tid === false || $tid <= 0) {
            $log->warn("Could not retrieve the ID for " . $tstr);
            return false;
        }

        $aq = AquariusSoap::getInstance();
        $res = $aq->deleteTimeseries($tid);

        return $res;
    }

}
