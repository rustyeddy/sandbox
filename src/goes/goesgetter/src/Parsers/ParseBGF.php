<?php

namespace SierraHydrog\Goes\Parsers;

class ParseBGF extends Parser {

    /**
     * @param $site the Site object
     * @param $data the raw data including the header
     * @return mixed the values and sensors.
     */
    public function parseBody( &$data, $site ) {
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
    }
}
