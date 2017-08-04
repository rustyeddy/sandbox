<?php

namespace SierraHydrog\Goes\Commands;


class SiteCmd extends CommandBase
{
    public function __construct()
    {
        $this->command_name = "site";
        $this->helpstr = "Interact with GOES sites";

        $this->commands = [
            'list' => [
                'usage'  => 'goes site list',
                'help'   => 'list sites we have configured',
                'proc'   => 'listAll',
            ],
            'convert' => [
                'usage'  => 'goes site convert',
                'help'   => 'convert current version of sites.json to new',
                'proc'   => 'convert2',
            ],
            'netlist' => [
                'usage'  => 'goes site netlist',
                'help'   => 'generate a DCSToolkit compatible network list',
                'proc'   => 'netList',
            ],
            'describe' => [
                'usage'  => 'goes site describe <siteId>',
                'help'   => 'describe in detail a site',
                'proc'   => 'describe',
            ],
            'missing'  => [
                'usage'  => 'goes site missing',
                'help'   => 'show missing timeseries for all sites',
                'proc'   => 'missingTimeseries',
            ],
        ];
    }

    /**
     * List all sites given optional filters.
     *
     * @param $args filter which sites to list
     * @return JSON encoded string
     */
    public function listAll($args, $params = null) {
        global $config;

        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        $sites = $sm->getSites();
        if ($sites == null) {
            return false;
        }

        $verbose = ($params && $params['verbose']) ?
            true : false;

        $output = [];
        switch ($config['output']) {
            case 'text':
                foreach ($sites as $s) {
                    $output[] = sprintf("%9s: %-9s %-10s => %s",
                        $s->getMnemonic(), $s->getNesdisId(),
                        (array_key_exists('locationId', $s)) ?
                            $s->locationId : "NULL",
                        $s->getShefOrder());
                    if ($verbose) {
                        foreach ($s->timeseriesIds as $tstr) {
                            $output[] = "\t\t\t" . $tstr;
                        }
                    }
                }
                $output = implode("\n", $output);
                break;
            case 'json':
                $output = json_encode($sites);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    /**
     * Generate a DCSToolkit network list.
     *
     * @param $args filter which sites to list
     * @return JSON encoded string
     */
    public function netList($args, $params = null) {
        global $config;

        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        $output = "";
        if ($params != null) {
            foreach ($params as $sname) {
                $s = $this->getSite($sname);
                $output .= $s->toNetworkList();
            }
        } else {
            $output = $sm->toNetworkList();
        }

        $verbose = ($params && $params['verbose']) ?
            true : false;

        switch ($config['output']) {
            case 'text':
            case 'json':
            case 'html':
                // TODO
                break;
        }
        return $output;
    }


    /**
     * Describe a single site.
     *
     * @param $args the site to describe.
     * @return string
     */
    public function describe($args, $params = null) {
        global $config;
        global $log;

        if (count($args) < 1) {
            $log->error('Must provide site');
            exit(-1);
        }

        $sid = array_shift($args);
        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        $site = $sm->getSite($sid);
        if (!$site) {
            $log->warn("Could not find site: " . $sid);
            return false;
        }

        $output = $site->describe();
        switch ($config['output']) {
            case 'text':
                $o = [];
                $s = json_decode($output, JSON_OBJECT_AS_ARRAY);
                foreach($s as $k => $v) {
                    if (is_scalar($v)) {
                        $o[] = sprintf("%20s: %-s", $k, $v);
                    }
                }
                $output = implode("\n", $o);
                break;

            case 'json':
                // $output is already json
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    /**
     * @param $args
     * @return mixed
     */
    public function missingTimeseries($args)
    {
        global $config;

        $sm = \SierraHydrog\Goes\SiteManager::getInstance();
        $sites = $sm->getSites();
        $allmissing = [];
        foreach ($sites as $site) {
            $missing = $site->getMissingTimeseries();
            if ($missing == false) {
                continue;
            }
            $ts[$site->getMnemonic()] = $missing;
            $allmissing[] = $missing;
        }

        // Now print if we are printing
        foreach ($allmissing as $mis) {
            foreach ($mis as $tstr) {
                $output[] = $tstr;
            }
        }
        switch ($config['output']) {
            case 'text':
                $output = implode("\n", $output);
                break;

            case 'json':
                $output = json_encode($output);
                break;

            case 'html':
                // TODO
                break;
        }

        return $output;
    }
}
