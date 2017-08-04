<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 6/8/16
 * Time: 7:57 AM
 */

namespace SierraHydrog\Goes;


class StreamFactory
{
    static private $_instance;

    private $proc;
    private $queue = [];

    private $rkey = 'goesraw';
    private $rfailkey = 'goesraw-failed';

    private $mkey = 'measurements';
    private $mkeyFail = 'measurements-failed';

    static public function getInstance()
    {
        if (StreamFactory::$_instance == null) {
            StreamFactory::$_instance = new StreamFactory();
        }

        return StreamFactory::$_instance;
    }

    /**
     * Get a Stream of data directly from DCS Toolkit.
     *
     * We'll have the raw data processed into timeseries blocks and store in the cache.
     */
    public function getStreamData()
    {
        global $config;
        $blocks = 0;

        $dcsuser = $config['dcsuser'];
        $dcspass = $config['dcspass'];
                 
        // Replace below with config variables.
        $cmd = "/srv/LrgsClient/bin/getDcpMessages" .
             " -u " . $dcsuser . " " .
             " -P " . $dcspass . " " .
             " -h cdadata.wcda.noaa.gov " .
             " -f etc/dcstool/procs/all-realtime.sc " .
             " -a '====' -x " .
             " -t 3600";

        output("Getting data from GOES LRGS Site.\n");
        output("Using this command: $cmd\n");
        $proc = popen($cmd, "r");
        if (!$proc) {
            $eray = error_get_last();
            print "Error opening: " . $cmd . "\n";
            print_r($eray);
            exit;
        }
        $delim = $config['delimiter'];
        $block = null;
        while ($proc && !feof($proc)) {

            $line = "";
            if (($line = fgets($proc, 8192)) === false) {
                $eray = error_get_last();
                print "Error getting next line: \n";
                print_r($eray);
                continue;
            }
            $line = trim($line);
            if (strlen($line) < 1) continue;

            if (!strncmp($line, $delim, strlen($delim))) {
                $this->cacheBlock($block);
                $blocks++;
                $block = null;
            } else {
                $block .= (($block) ? '|' : '') . $line;
            }
        }

        output("Cached $blocks new blocks for parsing");
    }

    /**
     * Process a file filled with a stream of measurements.
     *
     * @param $fname name of the file with stream data.
     */
    public function getStreamFile($fname)
    {
        global $config;
        $blocks = 0;

        output("Fetching raw data from file: " . $fname);
        $content = file_get_contents($fname);
        $lines = explode("\n", $content);

        $delim = $config['delimiter'];
        $block = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) < 1) {
                continue;
            }

            if (!strncmp($line, $delim, strlen($delim))) {
                $blocks++;
                $this->cacheBlock($block);
                $block = null;
            } else {
                $block .= (($block) ? '|' : '') . $line;
            }
        }

        output("Cached $blocks new blocks for parsing");
    }

    /* ------------------------------------------------------------------------------ */

    /**
     * Save a raw GOES block in cache.  This could be further optimized by pushing
     * more than one block at a time.
     *
     * @param $block
     * @return mixed
     */
    private function cacheBlock($block)
    {
        $red = Redis::getClient();
        $ok = $red->rpush($this->rkey, $block);

        output("Cached block: " . $block . "\n");
        return $ok;
    }


    /* -----------------  Operations Reading From the Cache --------------------- */


    /**
     * Parse Raw Data blocks stored in the Cache.
     */
    public function parseRawData()
    {
        global $log;

        $red = Redis::getClient();

        while (true) {

            // Block for about 3 minutes or so..
            $retval = $red->blpop($this->rkey, 360);
            if ($retval == null) {
                // We are done reading...
                return true;
            }

            $blockstr = $retval[1];
            $block = explode('|', $blockstr);
            $ok = $this->parseBlock($block);
            if (!$ok) {
                print_r(error_get_last());
                exit(-2);
            }
        }
    }

    /**
     * Parse a raw GOES block.
     *
     * @param $block
     * @return mixed
     */
    private function parseBlock($block)
    {
        global $log;

        $parser = Parsers\Parser::getInstance();
        $sm = SiteManager::getInstance();

        $header = $parser->parseHeader(array_shift($block));
        $site = $sm->getSiteFromNesdisId($header['nesdisid']);

        $str = sprintf("Parsing site: %s - %s at %s\n",
            $site->getMnemonic(), $header['nesdisid'], $header['timestamp']);
        output($str);

        $red = Redis::getClient();
        assert($red);

        $body = $parser->parseBody($block, $site);
        foreach ($body as $tsid => $vals) {
            $measurments = Measurement::getMeasurements($site->getMnemonic(),
                                                        $header['timestamp'], $body);
            $json = json_encode($measurments);
            $ok = $red->rpush($this->mkey, $json);
            if (!$ok) {
                $earr = error_get_last();
                print_r($earr);
                exit;
            }
            output("Just cached $tsid for a total $ok measurements\n");
        }
        return true;
    }

    /* --------------------------- Store Measurements -------------------------- */

    public function storeMeasurements() {

        $red = Redis::getClient();

        while (true) {

            // Block for about 3 minutes or so..
            $retval = $red->blpop($this->mkey, 360);
            if ($retval == null) {
                // We are done reading...
                return true;
            }

            $json = $retval[1];
            $meas = json_decode($json);

            $tm = TimeseriesManager::getInstance();
            foreach ($meas as $m) {
                $res = $tm->storeTimeseries($m);
                output($m->timestamp . " Attempted saving " . $m->tsid .
                    " points appended " . $res['numPointsAppended'] .
                    " append token: " . $res['appendToken'] . "\n");

                if ($res['tid'] == 0) {
                    $this->saveFailedMeasurement($res, $json);
                }
            }
        }
        return true;
    }

    /**
     * Save the failed measurement for later.
     *
     * @param $res
     * @param $json
     */
    private function saveFailedMeasurement($res, $json) {
        $red = Redis::getClient();
        $red->rpush($this->mkeyFail, $json);
    }
}
