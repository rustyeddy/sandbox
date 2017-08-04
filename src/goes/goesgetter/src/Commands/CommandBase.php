<?php

namespace SierraHydrog\Goes\Commands;


abstract class CommandBase
{
    // A Single line help string
    protected $helpstr = "TODO: NEED TO SET HELP STRING";

    // Associative array with the command name and command object
    protected $commands = [];

    // Our command Name
    protected $command_name = null;

    // Set the output format we are expecting.
    protected $output = 'text';

    // Set the named parameters
    protected $params = [];

    // Set the un named arguments
    protected $args = [];

    // Constructor to create commands
    protected function __construct()
    {
        /*
        $this->command_name = "some-command";
        $this->commands = [
            'cmd_name' => [
                'usage' => "cmd_name <required-arg> [optional-arg] [named=arg]",
                'help'  => "briefly what the command does",
                'proc'  => "procedure to call",
            ],
            'cmd2' => [
                'usage' => "cmd2 <reqarg>",
                'help'  => "this arg does something",
                'proc'  => "proc2"
            ],
        ];
        */
    }

    /**
     * Execute the command
     */
    public function exec($args, $params = null)
    {
        global $log;

        if (count($args) < 1) {
            $log->error("Not enough args passed to us");
            exit(-1);
        }

        $cmdstr = array_shift($args);
        if ($cmdstr == "help") {
            $res = $this->usage();
            return $res;
        }

        if (!array_key_exists($cmdstr, $this->commands)) {
            $log->error($this->command_name . " uknown command " . $cmdstr);
            $res = $this->usage();
            return $res;
        }

        $cmd = $this->commands[$cmdstr];
        $proc = $cmd['proc'];

        $output = $this->$proc($args, $params);
        return $output;
    }

    /**
     * Print out the help for this command
     */
    public function help() {
        $help = [];
        foreach ($this->commands as $c => $v) {
            $help[] = sprintf ("%20s: %-s", $c, $v['help']);
        }

        $output = "";
        switch ($this->output) {
            case 'text':
                $output = implode("\n", $help);
                break;

            case 'json':
                $output = json_encode($help, true);
                break;

            case 'html':
                // TODO:
                break;
        }
        return $output;
    }

    /**
     * Print out usage
     */
    public function usage()
    {
        $usage = [];
        foreach ($this->commands as $c => $v) {
            $usage[] = sprintf("%-40s: %s",
                "goes $this->command_name $c", $v['usage'], $v['help']);
        }

        $output = "";
        switch ($this->output) {
            case 'text':
                $output = implode("\n", $usage);
                $output .= "\n";
                break;

            case 'json':
                $output = json_encode($usage, true);
                break;

            case 'html':
                // TODO
                break;
        }
        return $output;
    }

    /**
     * Split the incoming arguments into a associative array.
     *
     * @param $args
     */
    public static function splitArgs(&$args)
    {
        $params = [];
        $nargs = count($args);
        for ($i = 0; $i < $nargs; $i++) {
            $arg = $args[$i];

            $eq = strpos($arg, '=');
            if ($eq) {
                $data = explode('=', $arg);
                $params[$data[0]] = $data[1];
                unset($args[$i]);
            }
        }
        return $params;
    }

    /**
     * @param $arg the named parameter to look for.
     * @return the param value if it exists false otherwise.
     */
    public function getParameter($arg, $params = null)
    {
        if ($params && array_key_exists($arg, $params)) {
            return $params[$arg];
        }
        if (array_key_exists($arg, $this->params)) {
            return $this->params[$arg];
        }
        return false;
    }

    /**
     * @param $args
     */
    public function describe($args)
    {
        $ref = ReflectionClass($this);
        foreach ($ref->getProperties() as $prop) {
            $name = $prop->name;
            printf("%20s: %-s\n", $name, $this->$name);
        }
    }

}
