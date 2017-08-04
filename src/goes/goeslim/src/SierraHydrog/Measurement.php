<?php

namespace SierraHydrog;

use Carbon\Carbon;

class Measurement {

	// The values of this measurement
	public $siteId    = "";
	public $timestamp = "";
	public $tsid	  = "";
	public $value 	  = "";
    public $result    = null;

	// Private 
	private $incr 	  = 15;

	/**
	 * Create a new array of proper measurement values.
	 * 
	 * @param String $sid SiteId aka Site::$mneumonic
	 * @param String $t   Timestamp
	 * @param String $tsid Timeseries ID
	 * @param Mixed  $v   value of measurement, typically a floating point
	 */
	public function __construct($sid, $ts, $tsid, $v) {

		// Set these values straight away
		$this->siteId = $sid;
		$this->tsid = $tsid;
		$this->value = $v;
		$this->timestamp = $ts;
	}

    /**
     * Create a time string from the timedata.  Increment the 
     * data according the incr variable, which will be 15 minutes.
     *
     * This function needs to take care of rolling over the hour,
     * that is 54 + 15 minutes = 09 with one hour later.
     */
    public static function createTimeString($timestamp, $incr = 0)
    {
		if ($incr === 0) {
			return $timestamp;
		}

		$c = new \Carbon\Carbon($timestamp);
		$c->addMinutes($incr);
        $ts = $c->toDateTimeString();
		$c->gt(Carbon::now(new \DateTimeZone('UTC')));
		return $ts;
    }

    /**
     * Get an array of Measurement objects that represent the values
     * we have been sent.
     *
     * @param  string  $sid    SiteID aka mneumonic
     * @param  string  $ts     Timestamp
     * @param  array   $values value array
     * @param  integer $tinc   time increment almost always 15min
     * @return array           an array of measurement objects
     */
	public static function getMeasurements($sid, $ts, $tsid, $values, $incr = 15) {

        $ms = array();
		foreach($values as $v) {
			$tstamp = $ts;

            $m = new Measurement($sid, $tstamp, $tsid, $v);
            $m->timestamp = $tstamp;
            $tstamp = Measurement::createTimeString($tstamp, $incr);
            if (!$tstamp) {
                return false;
            }
            $ms[] = $m;
		}
		return $ms;
	}
}
