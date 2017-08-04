<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 2/28/16
 * Time: 11:27 AM
 */

namespace SierraHydrog\Goes;


class DataFactory
{
    // Array of sites to process, sites will also manage
    private $sites = [];

    // Max number of entries to process
    private $count = -1;

    // Analyze the files, counts, dates, etc.
    private $analyze = true;

    // Are we going to store in AQUARIUS
    private $storeFiles = true;

    // The list of files we need to process
    private $fileList = [];

    // Files with parse errors
    private $parseErrorList = [];

    // Files with storage errors
    private $storageErrorList = [];

    // File directory
    private $fileDirectory = null;

    private $blocks = [];

    /**
     * @return array
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param array $sites
     */
    public function setSites($sites)
    {
        $this->sites = $sites;
        return $this;
    }

    /**
     * @param $site
     * @return $this
     */
    public function addSite($site)
    {
        global $log;

        $sm = SiteManager::getInstance();
        if (!$sm->getSite($site)) {
            $log->fatal("Unknown site: " . $site);
            die("Could not find site: " .$site);
        }
        $this->sites[] = $site;
        return $this;
    }

    /**
     * @param $sites array or single string.
     */
    public function addSites($sites)
    {
        if (is_array($sites)) {
            foreach ($sites as $s) {
                $this->addSite($s);
            }
        } else {
            $this->addSite($sites);
        }
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAnalyze()
    {
        return $this->analyze;
    }

    /**
     * @param boolean $analyze
     */
    public function setAnalyze($analyze)
    {
        $this->analyze = $analyze;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isStoreFiles()
    {
        return $this->storeFiles;
    }

    /**
     * @param boolean $storeFiles
     */
    public function setStoreFiles($storeFiles)
    {
        $this->storeFiles = $storeFiles;
        return $this;
    }

    public function setFileDirectory($dir) {
        $this->fileDirectory = $dir;
        return $this;
    }

    public function getFileDirectory() {
        return $this->fileDirectory;
    }

    /**
     * Get the files we need for processing.
     *
     * @return array of files or null if empty.
     */
    public function getFiles($fdir = null)
    {
        global $log;

        $fm = \SierraHydrog\Goes\FileManager::getInstance();
        $sm = \SierraHydrog\Goes\SiteManager::getInstance();

        $globstr = null;

        if (!$this->sites || count($this->sites) == 0) {
            $this->sites = $sm->getSites();
        }

        $filedir = ($fdir) ? $fdir : $this->fileDirectory;
        foreach ($this->sites as $siteid) {

            if (is_object($siteid)) {
                $site = $siteid;
            } else {
                $site = $sm->findSite($siteid);
            }
            if (!$site) {
                $log->fatal("Failed to locate site: " . $siteid);
                exit(2);
            }

            // Get the search string
            $globstr = $site->getNesdisId();
            if ($globstr === null) {
                $log->fatal("Unknown NESDIS: " . $site->getMnemonic());
                exit(3);
            }

            // Get all files that match this search string
            $flist = array_values($fm->getFiles($globstr, $filedir, $this->count));
            if (!$flist) {
                $log->debug("Now files for " . $globstr);
                $this->fileList[$globstr] = [];
                continue;
            }
            $this->fileList[$globstr] = $flist[0];
        }
        return $this->fileList;
    }

    /**
     * @param $filesarray array of files indexed by NESDIS ID to be processed.
     */
    public function getMeasurements()
    {
        global $log;

        $fm = FileManager::getInstance();
        $sm = SiteManager::getInstance();

        $success = true;
        $measurements = [];

        foreach ($this->fileList as $nesdis => $flist) {

            $site = $sm->getSiteFromNesdisId($nesdis);
            if ($site == null) {
                $log->error("Could not find site for NESDIS: " . $nesdis);
                continue;
            }

            // Get the parser for this file.
            $parser = \SierraHydrog\Goes\Parsers\Parser::getInstance();
            if (!$parser) {
                $log->error("We don't know what parser to use for " . $site->getMnemonic());
                continue;
            }

            // Now start walking all the files.
            foreach ($flist as $file) {

                $file->setSiteName($site->getMnemonic());
                $filename = $file->getFilename();

                $log->debug("Processing file: " . $filename);

                // Now parse the file
                $meas = $parser->parse($file, $site);
                if ($meas == null) {
                    $log->error("Storing errored file in errordir: " .
                        $file->getFullpath());
                    $this->parseErrorList[] = $file;
                    $fm->moveToFaildir($file);
                    continue;
                }
                $file->setMeasurements($meas);
            }
        }
    }


    /**
     * @return string
     */
    public function analyzeMeasurements()
    {
        if (!$this->analyze) {
            return "";
        }
        $output = "";
        foreach ($this->fileList as $nesdis => $files) {
            //$output .= $nesdis . "\n";
            foreach ($files as $fdata) {
                $output .= "\t" . $fdata->getFilename() . "\n";
                foreach ($fdata->getMeasurements() as $meas) {
                    $output .= sprintf("%25s: %10s %5d\n",
                        $meas->timestamp, $meas->tsid, $meas->value);
                }
            }
        }
        return $output;
    }

    /**
     * @param $mesasurements
     */
    public function storeMeasurements()
    {
        global $config;
        global $log;

        if (!$this->storeFiles) {
            return false;
        }

        $results = [];
        $fm = FileManager::getInstance();
        $tm = \SierraHydrog\Goes\TimeseriesManager::getInstance();
        $storedMeasurements = 0;
        $filesProcessed = 0;
        $failures = 0;
        $failuresInARow = 0;

        $output = "";
        foreach ($this->fileList as $nesdis => $files) {
            foreach ($files as $fdata) {

                foreach ($fdata->getMeasurements() as $meas) {

                    $res = $tm->storeTimeseries($meas, $fdata);
                    if (!$res) {

                        $failures ++;
                        $failuresInARow++;
                        $this->storageErrorList[] = $fdata;
                        $fm->moveToFaildir($fdata);
                        $log->error("Failed to store measurements for: " .
                            $fdata->getFilename());
                        if ($failuresInARow > 24) {
                            $log->fatal("Too many persistant failures");
                            die("We seem to be having persistant failures please check error logs\n");
                        }
                        continue;
                    }
                    $failuresInARow = 0;
                    $tm->recordAppend($res, $fdata);
                    $storedMeasurements++;
                }
                $filesProcessed++;
                $fm->archiveFile($fdata);
            }
        }

        $output = "";
        switch($config['output']) {
            case 'text':
                $output[] = "\nTotal files processed: " . $filesProcessed;
                $output[] = "  measurements stored: " . $storedMeasurements;
                $output[] = "    with parse errors: " . count($this->parseErrorList);
                $output[] = "  with storage errors: " . count($this->storageErrorList);
                if (array_key_exists('failed', $results)) {
                    $output[] = "      files errored:   ";
                    foreach ($results['failed'] as $reason => $files) {
                        foreach ($files as $fname) {
                            $output[] = sprintf("\t%20s: %-s", $reason, $fname);
                        }
                    }
                }
                $output = implode("\n", $output);
                break;

            default;
            case 'json':
                $output = json_encode($results);
                break;
        }
        return $output;
    }

    /**
     * Execute the get command.
     */
    public function countFiles()
    {
        global $config;

        foreach ($this->sites as $site) {
            $this->getFiles($site);
        }

        $total = 0;
        foreach ($this->fileList as $n => $a) {
            $total += count($a);
            $results[$n] = count($a);
        }
        $results['total'] = $total;
        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        return $results;
    }

    // Remove all files for which we know we do not have a timeseries for.
    private function filterMissingTimeseries($nlist) {
        global $siteManager;
        global $log;

        // Array of sites that are ready to be processed.
        $processSites = array();

        // Get the NESDIS ID of all files ready to be processed.
        $allnesdis = array_keys($nlist);

        // Walk all site and check that their timeserieses are available.
        foreach ($allnesdis as $n) {

            // Get the Site object with the given NESDIS ID.
            $site = $siteManager->getNesdis( $n );
            if (!$site) {
                $log->debug("Could not find a site for NESDISID: $n");
                continue;
            }

            $log->debug("Checking site for timeseries: " . $site->getMnemonic());

            // Lets see if there are any missing Timeseries, if so save them and move on.
            $missing = $site->getMissingTimeseries();
            if ( count( $missing ) > 0 ) {
                global $fileManager;

                $fileManager->saveMissingTimeseries( $site );
                continue;
            }

            // Save the files to the Site files queue for later processing.
            $site->addFiles( $nlist[ $n ] );
            $processSites[] = $site;
        }

        return $processSites;
    }

}
