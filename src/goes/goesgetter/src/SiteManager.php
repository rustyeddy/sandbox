<?php

namespace SierraHydrog\Goes;

/**
 * Class SiteManager: read the sites.json config file, allow other
 * classes to look up the sites.
 */
class SiteManager {

    private static $instance = null;
    
    private $collection = 

    private $sites = array();
    private $nesdisid = array();

    /*
     * Site keys are the keys that get saved to the datastore (json files, DB, etc),
     * The initial portion are from the PDT, the last three are optional:
     *
     * shefDescriptions: are the descriptions of the SHEF code stored in AQUARIUS
     * locationId:       Internal Identifier AQUARIUS stores it's ID in
     * parser:           custom parser used to parse data files dependent on DCS
     */
    private $siteKeys = array(
        "siteName",
        "mnemonic",
        "projectId",
        "pdtType",
        "nesdisId",
        "decodingScheme",
        "scanInterval",
        "shefOrder",
        "timeseriesIds",
        "specialRemarks",
        "latitude",
        "longitude",
        "elevation",

        // Some additional stuff possible.
        "shefDescriptions",
        "locationId",
        "parser"
    );

    // Hmmm.
    public $problemFiles = array();

    /**
     * @return Site instance.
     */
    static public function getInstance() {
        if (null === SiteManager::$instance) {
            SiteManager::$instance = new SiteManager();
            SiteManager::$instance->init();
        }
        return SiteManager::$instance;
    }

    // Util class
    private function __construct() { }

    // Initialize the instance.
    private function initFile() {
        global $config;
        $this->sitesfile = $config['sitesfile'];
        $this->readSitesConfig();
    }

    /**
     * @return array of Sites
     */
    public function getSitesFile() {
        if ($this->sites == null) {
            $this->readSitesConfig();
        }
        return $this->sites;
    }

    /**
     * @return array siteKeys.
     */
    public function getSiteKeys() {
        return $this->siteKeys;
    }

    // Read sites.json index according to site mnemonic and NESDISID
    private function readSitesConfigFile() {
        global $config;
        global $log;
        global $basedir;

        $jstr = file_get_contents( $basedir . $config['sitesfile'] );
        $json = json_decode($jstr, true);
        if (null === $json) {
            $log->error("Error reading sites file: " . json_last_error_msg());
            return false;
        }

        foreach ($json as $j) {
            $site = new Site();
            $site->fromArray($j);
            $this->sites[ $j['mnemonic'] ] = $site;
            $this->nesdisid[ $j['nesdisId'] ] = $site;
        }
        return true;
    }

    /**
     * Return the site structure based on the PDT mnemonic.
     *
     * @param $id PDT mnemonic
     * @return Site   the site corresponding to the mnemonic
     */
    public function getSite($id) {
        if ($this->sites == null) {
            $this->getSites();
        }

        if ( array_key_exists($id, $this->sites)) {
            return $this->sites[$id];
        }
        return null;
    }

    /**
     * Return the Site structure based on the GOES NESDISID.
     *
     * @param $id    the NESDISID
     * @return Site  the Site objected based on the corresponding NESDISID.
     */
    public function getNesdis( $id ) {
        global $log;

        if ( array_key_exists( $id, $this->nesdisid )) {
            return $this->nesdisid[ $id ];
        }

        $log->warn("Could not find site with NESDIS ID: " . $id );
        return null;
    }

    /**
     * Try to find a site via either the mnemonic or the site id.
     *
     * @param $id - site id or mnemonic
     * @return - site object or null if it does not exist.
     */
    public function findSite($id)
    {
        if (array_key_exists($id, $this->sites)) {
            return $this->sites[$id];
        }

        if (array_key_exists($id, $this->nesdisid)) {
            return $this->nesdisid[$id];
        }

        return null;
    }


    /**
     * @param $nesdis
     * @return null
     */
    public function getSiteFromNesdisId($nesdisid)
    {
        if (array_key_exists($nesdisid, $this->nesdisid)) {
            return $this->nesdisid[$nesdisid];
        }
        return null;
    }

    /**
     * Return all SiteIDs we know about.
     *
     * @return array SiteId's
     */
    public function getSiteIds() {
        return array_keys( $this->sites );
    }

    /**
     * Generate a DCSToolKit compatible NetworkList.
     */
    public function toNetworkList() {
        $str = "";
        foreach ($this->sites as $site) {
            $str .= $site->toNetworkList() . "\n";
        }
        return $str;
    }

    // No comment
    public function toString() {
        $str = "";
        foreach ($this->sites as $site) {
            $str .= $site->toString() . "\n";
        }
        return $str;
    }
}
