<?php

namespace SierraHydrog\Goes;

class SHEFMap {

    // static instance
    private static $instance = null;

    // Map of SHEF code to long description
    private $shefMap = null;

    // SHEF Map filename
    private $filename = 'etc/shefmap.json';

    // AQUARIUS parameters
    private $aqParameters = null;

    // No public SHEF map
    protected function ShefMap() { }

    /* --------------------- Public Function ------------------ */

    /**
     * get the SHEPMap instance, create one if it does not
     * already exist.  Also read the SHEFMap if it is null.
     */
    public static function getInstance() {
        if (null === SHEFMap::$instance) {
            SHEFMap::$instance = new SHEFMap();
        }
        return SHEFMap::$instance;
    }

    /**
     * Get the SHEF map file, if it is null read it from
     * the JSON file.
     *
     * @return shefMapFile;
     */
    public function getShefMap() {
        global $config;
        global $log;

        if ($this->shefMap == null ) {

            $sfile = $config['shefMap'];

            $log->debug("Reading SHEF map from: " . $sfile);
            $json = file_get_contents($sfile);
            $this->shefMap = json_decode($json, true);
            if (!$this->shefMap) {
                $errmsg = json_last_error_msg();
                $log->error("Error decoding SHEFMap: " . $errmsg);
                return false;
            }
        }
        return $this->shefMap;
    }

    /**
     * Get the description of the SHEF code.
     *
     * @param $shef
     * @return bool
     */
    public function getShefCode($shef)
    {
        global $log;

        $map = $this->getShefMap();
        if (!$map) {
            $log->fatal("Could not open the SHEF map");
            return false;
        }

        if (! array_key_exists($shef, $map) ) {
            $log->warn("ERROR retrieving description for unknown SHEF code: ". $shef);
            return false;
        }

        $item = $map[$shef];
        return $item;
    }

    /**
     * Get the long description for the given SHEF code.
     *
     * @param  [String] SHEF code
     * @return [String] SHEF long code
     */
    public function getDescription($shef) {
        global $log;

        $descr = null;

        $item = $this->getShefCode($shef);
        if ($item) {
            $descr = "";
            if (is_array($map->$shef)) {
                $arr = $map->$shef;
                $descr = $arr[0];
            } else {
                $descr = $map->$shef;
            }
        }
        return $descr;
    }

    /**
     * @return parameters
     */
    public function getAquariusParams()
    {
        global $log;

        if ($this->aqParameters === null) {
            $aq = AquariusRest::getInstance();
            $params = $aq->getParameters();
            if ($params === false) {
                $log->warn("Unable to retrieve AQUARIUS paramenters");
                return false;
            }
            $this->aqParameters = $params->Parameters;
        }
        return $this->aqParameters;
    }
}
