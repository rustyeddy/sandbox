<?php

namespace SierraHydrog\Goes\Commands;

use SierraHydrog\Goes\DataFactory;
use SierraHydrog\Goes\FileManager;
use SierraHydrog\Goes\SiteManager;

class DataCmd extends CommandBase
{
    // Keep a list of the files we will need to process.
    private $filesToProcess = null;

    // Keep track of failed files
    private $filesThatFailed = null;

    private $dryrun = false;

    // A constructor
    public function __construct()
    {
        $this->command_name = "data";
        $this->helpstr = "process files from DCSToolkit load into AQUARIUS";

        $this->commands = [
            'stream' => [
                'usage' => "fetch data from LRGS [stream] [file=fname]",
                'help'  => 'fetch data using DCSTOOL kit',
                'proc'  => 'fetchStream',
            ],
            'count' => [
                'usage' => 'goes count data [siteid ...]',
                'help'  => 'count the number of files to process',
                'proc'  => 'justCount',
            ],
            'store' => [
                'usage' => 'goes data store [siteid={siteid}] [count=#] [dryrun=true]',
                'help'  => 'store current retrieved data',
                'proc'  => 'process',
            ]
        ];

        $this->params['count'] = -1;
        $this->params['sites'] = [];
        $this->params['analyze'] = false;
    }

    /**
     * Process data that has already been fetched
     *
     * @param $args
     * @param null $params
     * @return string
     */
    public function process($args, $params = null) {
        global $config;
        global $log;

        $analyze = $this->getParameter('analyze', $params);
        $count = $this->getParameter('count', $params);
        $sites = count($args) ? $args : null;

        $df = new DataFactory();
        $df->setAnalyze($analyze)
            ->setCount($count)
            ->setStoreFiles(true);

        if (array_key_exists('dir', $params)) {
            $df->setFileDirectory($params['dir']);
        }

        if ($sites) {
            $df->addSites($sites);
        }

        // Get the files to be processed
        $df->getFiles();

        // Now get the measurements
        $df->getMeasurements();

        // Now get an analysis based on the measurements
        $output = $df->analyzeMeasurements();

        // Save measurements if we are tasked to.
        $output .= $df->storeMeasurements();

        return $output;
    }


    /**
     * Execute the get command.
     */
    public function justCount($args, $params = null)
    {
        global $config;
        global $log;

        $analyze = $this->getParameter('analyze', $params);
        $count = $this->getParameter('count', $params);

        $nfdir = $config['newfiledir'];
        $faildir = $config['faildir'];
        $archdir = $config['archivedir'];
        $allfiles = [];

        $sm = SiteManager::getInstance();
        foreach($sm->getSites() as $s) {
            $n = $s->getNesdisId();

            $allfiles[$n] = [];
            $allfiles[$n]['newfiles'] = 0;
            $allfiles[$n]['fails'] = 0;
            $allfiles[$n]['archived'] = 0;
        }

        $it = new \FilesystemIterator($nfdir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $finfo) {
            $n = substr($finfo->getFilename(), 0, 8);
            if (!array_key_exists($n, $allfiles)) {
                continue;
            }
            $allfiles[$n]['newfiles']++;
        }

        $it = new \FilesystemIterator($faildir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $finfo) {
            $n = substr($finfo->getFilename(), 0, 8);
            if (!array_key_exists($n, $allfiles)) {
                continue;
            }
            $allfiles[$n]['fails']++;
        }

        $it = new \FilesystemIterator($archdir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $finfo) {
            $n = substr($finfo->getFilename(), 0, 8);
            if (!array_key_exists($n, $allfiles)) {
                continue;
            }
            $allfiles[$n]['archived']++;
        }

        $output = "";
        switch($config['output']) {
            case 'text':
                $sm = SiteManager::getInstance();
                $output .= sprintf("%6s %10s: %8s, %8s, %8s\n", "Siteid", "Nesdis", "New", "Failed", "Archived");

                foreach($allfiles as $nes => $counts) {
                    $site = $sm->getNesdis($nes);
                    if ($site) {
                        $sid = $site->getMnemonic();

                        $n = array_key_exists('newfiles', $counts) ? $counts['newfiles'] : 0;
                        $f = array_key_exists('fails', $counts) ? $counts['fails'] : 0;
                        $e = array_key_exists('archived', $counts) ? $counts['archived'] : 0;

                        $output .= sprintf("%6s %10s: %8d, %8d, %8d\n", $sid, $nes, $n, $f, $e);
                    }
                }
                break;

            case 'html':
                // TODO:
                break;

            default;
            case 'json':
                $output = json_encode($allfiles);
                break;
        }
        return $output;
    }

    public function fetchStream($args, $params = null)
    {
        global $config;
        global $log;

        $fname = array_key_exists('file', $params) ?
            $params['file'] : null;

        if ($args > 0) {
            // We are going to stream
        }

        $this->fileStream($fname);


    }

    private function fileStream($fname) {
        $df = new DataFactory();

        $foo = $df->streamFileContents($fname);
        return $foo;
    }

    private function liveStream() {

        /*
         * -v verbose-mode
         * -d debug level
         * -l log-file name
         * -P password
         * -s single
         * -E extended
         *
         * XXX: Get -h from a file
         */
        $getdatacmd = $config['dcsdir'] . "bin/getDcpMessages" .
            " -u " . $config['dcsuser'] .
            " -h " . "cdadata.wcda.noaa.gov" .
            " -f " . $params['searchFile'] .
            " -a " . "====" .
            " -x ";

        $cwd = $config['newfiledir'];

        $pdesc = [
            0 => [ 'pipe', 'r' ],
            1 => [ 'pipe', 'w' ],
            2 => [ 'file', '/tmp/goesgetter-errors.txt', "a"]
        ];

        /*
         * Open up the pipe and start sucking the data in.
         */
        $pipe = proc_open($getdatacmd, $pdesc, $pipes, $cwd);
        if (!is_resource($pipe)) {
            $lerror = get_last_error();
            $log->fatal("Failed to retrieve GOES data: " . $lerror['message'] . " " .
                $lerror['file'] . ":" . $lerror['line']);
            return false;
        }
        $contents = stream_get_contents($pipes[1]);
        $pop = proc_close($pipe);

        return $contents;
    }
}
