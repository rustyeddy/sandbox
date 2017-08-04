<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 1/6/16
 * Time: 10:16 PM
 */

namespace SierraHydrog\Goes\Commands;

class ConfigCmd extends CommandBase
{

    public function __construct()
    {
        $this->command_name = "config";
        $this->helpstr = "Show and set configuration items";

        $this->commands = [
            'list' => [
                'usage' => '',
                'help'  => 'list out current config options',
                'proc'  => 'listConfig',
            ]
        ];
    }

    /**
     * List the current configuration options.
     */
    public function listConfig($args = null)
    {
        global $config;
        $output = "";

        switch ($config['output']) {
            case 'text':
                foreach ($config as $k => $v) {
                    $output .= sprintf ("%20s: %-s\n", $k, $v);
                }
                break;

            case 'json':
                $output = json_encode($config);
                break;

            case 'html':
                // TODO: create the html output
                break;
        }
        return $output;
    }
}
