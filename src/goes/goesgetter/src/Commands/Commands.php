<?php

namespace SierraHydrog\Goes\Commands;

class Commands
{
    private static $instance;

    // The list of commands
    protected $commands = [];

    public static function getInstance()
    {
        if (Commands::$instance === null) {
            Commands::$instance = new Commands();
        }
        return Commands::$instance;
    }

    /**
     * Commands constructor.
     */
    public function __construct() {
        $this->commands = [
            'config'     => new ConfigCmd(),
            'file'       => new FileCmd(),
            'location'   => new LocationCmd(),
            'data'       => new DataCmd(),
            'site'       => new SiteCmd(),
            'stream'     => new StreamCmd(),
            'timeseries' => new TimeseriesCmd(),
        ];
    }

    /**
     * @param $cmdstr the command we are trying to get.
     * @return the command object
     */
    public function getCommand($cmdstr)
    {
        if (!array_key_exists($cmdstr, $this->commands)) {
            return null;
        }
        return $this->commands[$cmdstr];
    }

    /**
     * Execute the command according to the arguments we have been given.
     *
     * @param $args the command are args to be processed.
     * @return $res the results of the command json.
     */
    public function process($args = null)
    {
        global $log;
        global $config;

        $cmdstr = array_shift($args);
        if ($cmdstr == "help") {
            $output = $this->usage();
            return $output;
        }

        $cmd = $this->getCommand($cmdstr);
        if (!$cmd) {
            $log->error("Uknown command: " . $cmd);
            return false;
        }

        $params = [];
        $params = CommandBase::splitArgs($args);

        // Save the output type if it was passed as a named parameter
        if (array_key_exists('output', $params)) {
            $config['output'] = $params['output'];
        }

        $output = $cmd->exec($args, $params);
        return $output;
    }

    /**
     * Print the usage of all these commands.
     */
    public function usage()
    {
        $output = "";
        foreach ($this->commands as $cmd) {
            $output .= $cmd->usage();
        }
        return $output;
    }
}
