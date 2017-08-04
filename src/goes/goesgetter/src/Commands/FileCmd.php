<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 1/20/16
 * Time: 4:57 PM
 */

namespace SierraHydrog\Goes\Commands;

class FileCmd extends CommandBase
{

    public function __construct()
    {
        $this->command_name = "file";
        $this->helpstr = "file system utilities";

        $this->commands = [
            'shefmap' => [
                'usage' => '',
                'help'  => 'list out current shef map',
                'proc'  => 'convertShefMap',
            ]
        ];
    }

    /**
     * Convert the shefmap to something else.  This is not a standard
     * command.
     *
     * TODO: move this to the bin directory.
     *
     * @param $args
     * @return bool|int
     */
    protected function convertShefMap($args)
    {
        global $log;
        global $config;

        $infile = 'etc/shef-official.json';
        $outfile = 'etc/shefmap.json';

        $json = file_get_contents($infile);
        if (!$json) {
            $log->error("Could not open file: " . $infile);
            return false;
        }

        $nary = [];
        $jary = json_decode($json, true);
        foreach ($jary as $k => $v) {
            $nary[$k]['long'] = $v;
            $nary[$k]['units'] = "";
        }
        $json = json_encode($nary, JSON_PRETTY_PRINT);
        $success = file_put_contents($outfile, $json);
        $log->debug("Converted shef-official to shef-map.json: success: " .
            $success);
        return $success;
    }
}
