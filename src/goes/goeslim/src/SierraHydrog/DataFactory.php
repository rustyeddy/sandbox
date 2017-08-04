<?php

namespace SierraHydrog;

class DataFactory
{
    use ContainerTrait;

    private $proc;
    private $queue = [];

    private $rkey = 'goesraw';
    private $rkeyFail = 'goesraw-failed';

    private $mkey = 'measurements';
    private $mkeyFail = 'measurements-failed';

    public function __construct() {
    }

    /*
     * ------------- Get Data From LRGS ----------------------------
     * 
     * 1. Get combined blocks of text from LRGS.  Multiple stations
     *    will come in one big chunk delimited by '===='.
     *
     * 2. Break large text block file into individual stations.
     *
     * 3. Store each station in Redis cache.
     *
     * -------------------------------------------------------------
     */

    /**
     * Get a Stream of data directly from DCS Toolkit.
     *
     * We'll have the raw data processed into timeseries blocks and store in the cache.
     */
    public function getLrgsData($u, $p)
    {
        $log = $this->logger();

        $blocks = 0;

        $lrgsuser = $u;
        $lrgspass = $p;
                 
        // Replace below with config variables.
        $cmd = "/srv/LrgsClient/bin/getDcpMessages" .
             " -u " . $u . " " .
             " -P " . $p . " " .
             " -h cdadata.wcda.noaa.gov " .
             " -f etc/all-realtime.sc " .
             " -a '====' -x " .
             " -t 3600";

        $proc = popen($cmd, "r");
        if (!$proc) {
            $eray = error_get_last();
            $log->error("Error: " . $eray . " " . $cmd);
            exit;
        }
        $delim = "====";
        $block = null;
        while ($proc && !feof($proc)) {

            $line = "";
            if (($line = fgets($proc, 8192)) === false) {
                $eray = error_get_last();
                $log->error("Error: processing data lines " . $eray);
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
        return $blocks;
    }

    /**
     * Count the number of block files that need to be processed
     */
    public function countBlockFiles() {
        $blockfiles = 0;
        $log = $this->logger();
        $settings = $this->settings();
 
        $blockdir = $settings['data']['rawdata'];
        if (! file_exists($blockdir) ) {
            $log->error("Data directory does not exist, exiting: " . $blockdir);
            return false;
        }

        // Only dive one level deep, add recursion later ...
       $files = scandir($blockdir);
        foreach ($files as $f) {
            $fullpath = $blockdir . '/' . $f;
            if ($f == "." || $f == ".." || 0 === filesize($fullpath)) {
                continue;
            }
            $blockfiles ++;
        }
        return $blockfiles;
    }

    /**
     * Process raw datablocks.  We'll want to rework this such that we can retrieve
     * datablocks from Redis or the filesystem.
     */
    public function processBlockFiles($count = 0)
    {
        $log = $this->logger();
        $settings = $this->settings();

        $blocks = 0;
        $move_process_blocks = true;

        // XXX: Ugly fix this dereferencing.
        $blockdir = $settings['data']['rawdata'];
        $log->debug("Scanning $blockdir for files to process");

        if (! file_exists($blockdir) ) {
            $log->error("Data directory does not exist, exiting: " . $blockdir);
            return false;
        }

        // Only dive one level deep, add recursion later ...
        $files = scandir($blockdir);

        $log->info("Scanned data dir: " . $blockdir . " and found " .
                   count($files) . " to be processed ");

        foreach ($files as $f) {
            $fullpath = $blockdir . '/' . $f;
            if ($f == "." || $f == ".." || 0 === filesize($fullpath)) {
                $log->debug("Skipping file $fullpath\n");
                continue;
            }
            $ret = $this->parseBlockFile($fullpath);
            $log->debug("Done parsing block file: " . $ret);
            $blocks++;

            // Following should only be false for testing & debugging
            if ($move_process_blocks) {
                // Archive the block
                $archdir = $settings['data']['archive'];
                $ret = rename($fullpath, $archdir . '/' . $f);
                if (! $ret) {
                    $log->error("Failed to move $fullpath to $archdir");
                }
            }
            if ($count > 0 && $blocks == $count - 1) {
                break;
            }
        }
        $log->info("Finished processing $count block files");
        return $blocks;
    }

    /**
     * Process a file filled with a stream of measurements.
     *
     * @param $fname name of the file with stream data.
     */
    public function parseBlockFile($fname)
    {
        $blocks = 0;

        $log = $this->logger();
        $log->info("Processing file: " . $fname);

        $content = file_get_contents($fname);
        $lines = explode("\n", $content);
        $delim = "====";
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
        return $blocks;
    }

    /**
     * Save a raw GOES block in cache.  This could be further optimized by pushing
     * more than one block at a time.
     *
     * @param $block
     * @return mixed
     */
    private function cacheBlock($block)
    {
        $log = $this->logger();
        $c = $this->cache();

        $log->debug("Cached block: ". $block);
        $ok = $c->rpush($this->rkey, $block);
        return $ok;
    }
    
    /* -----------------  Operations Reading From the Cache --------------------- 
     * 
     * 1. Get each chuck of data from the Redis cache.
     * 
     * 2. Parse the first line of the header to identify Site ID & Time.
     * 
     * 3. Parse the body of the file according to the Site ID.
     */

    /**
     * Parse Raw Data blocks stored in the Cache.
     */
    public function processMeasurements()
    {
        $log = $this->logger();
        $c = $this->cache();
        $results = [
            'success' => 0,
            'errors'  => 0
        ];
        $log->debug("Processesing Cached Measurements");
        $done = false;
        do {

            $log->debug(" ------------------- Next Cached Item --------------------");
            $items = $c->llen($this->rkey);
            $log->debug("Items left on the " . $this->rkey . " list: " . $items);
            if ($items == 0) {
                break;
            }

            $retval = $c->lpop($this->rkey);
            if ($retval) {
                $log->debug("Got measurement data: " . $retval);
                $blockstr = $retval;
                $block = explode('|', $blockstr);
                $ok = $this->parseMeasurement($block);
                if (!$ok) {
                    $log->error("Failed to parse: " . $block); 
                    $results['errors']++;
                } else {
                    $results['success']++;
                }
            } else {
                $done = true;
            }
            
        } while ( ! $done );
        return $results;
    }

    /**
     * TODO: This function will be used to keep a local (mongo) copy of the measurements
     */
    private function stashMeasurement($tsid, $ts, $vals) {
        $foo = explode(' ', $ts);
        $date = $foo[0];
        $time = explode(':', $foo[1])[0];
        $meas[$tsid][$date] = [
            $time => $vals,
        ];
        // TODO: write to mongo!
        print_r($meas); exit;
    }

    /**
     * Parse a raw GOES block.
     *
     * @param $block
     * @return mixed
     */
    public function testBGF()
    {
        $block = [];
        $block[] = "3A80121616271115435G49+0NN038WUP00035";
        $block[] = "0.04\n";
        $block[] = "0.04\n";
        $block[] = "0.04\n";
        $block[] = "0.04 12.5";

        $this->parseMeasurement($block);
        return ("{ 'result': '1' }");
    }
    private function parseMeasurement($block)
    {
        $log = $this->logger();
        $sm = $this->siteManager();
        $parser = $this->parser();
        $c = $this->cache();
        $pushed = 0;
        $errors = 0;

        $header = $parser->parseHeader(array_shift($block));
        assert($header);
        $nesdis = $header['nesdisid'];
        $log->debug("Parsing data for: " . $nesdis . " " . $header['timestamp']);

        // Now we need to find the site info for this corresponding NESDIS ID
        $site = $sm->getSiteFromNesdisId($header['nesdisid']);

        // Now try to parse the body!
        $log->debug($site->mnemonic . " SHEF order: " . $site->shefOrder);

        // Ignore HIDP
        if ($site->mnemonic == "HIDP" || $site->mnemonic == "WWC" ||
            $site->mnemonic == "MTM") {
            $log->warn(" !!!!! Ignoring HIDP !!!! ");
            return true;
        }

        $p = getBodyParser($site);
        $body = $p($block, $site);
        foreach ($body as $tsid => $vals) {

            # $this->stashMeasurement($tsid, $header['timestamp'], $vals);
            $measurements = Measurement::getMeasurements($site->getMnemonic(),
                                                        $header['timestamp'],
                                                        $tsid, $vals);
            $json = json_encode($measurements);
            $ok = $c->rpush($this->mkey, $json);
            if (!$ok) {

                $earr = error_get_last();
                $resp = [
                    "files_processed" => "0",
                    "last_error" => $earr,
                ];
                $log->error("Failed to get measurements: $earr");
                $errors++;
            } else {
                $pushed++;
            }
            $log->debug("Just cached $tsid for a total $ok measurements\n");
        }
        return [ 'pushed' => $pushed, 'errors' => $errors ];
    }

    /* --------------------------- Store Measurements -------------------------- */

    public function storeMeasurements($count = 0) {

        $c = $this->cache();
        $log = $this->logger();
        $tsm = $this->timeSeriesManager();
        $saved = 0;
        $fails = 0;
        $results = [];
        $res = null;
        
        while (true) {

            // Block for about 0 minutes or so..
            $retval = $c->blpop($this->mkey, 360);
            if ($retval == null) {
                return $retval;
            }
            $json = $retval[1];
            $meas = json_decode($json);
            foreach ($meas as $m) {

                /*
                 * this should store in REDIS and have a worker store away
                 * in AQUARIUS as well as our local Mongo DB.
                 */
                $res = $tsm->storeTimeseries($m);

                $log->debug($m->timestamp . " Attempted saving " . $m->tsid .
                    " points appended " . $res['numPointsAppended'] .
                    " append token: " . $res['appendToken'] . "\n");

                if ($res['tid'] == 0) {
                    $this->saveFailedMeasurement($res, $json);
                    $fails++;
                } else {
                    $saved++;
                }
            }
            $results[] = $res;
            
            # XXX: Broken!!!
            if ($count > 0 &&
                ($fails + $saved) >= $count) {
                break;
            }
        }
        $results['failed'] = $failed;
        $results['saved'] = $saved;
        return $results;
    }

    /**
     * Save the failed measurement for later.
     *
     * @param $res
     * @param $json
     */
    private function saveFailedMeasurement($res, $json) {
        $c = $this->cache();
        $c->rpush($this->mkeyFail, $json);
    }
}
