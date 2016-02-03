<?php

require_once 'utils.php';

class WP_CLI
{
    private $_wpcli;
    private $_user;
    public $_cwd;

    public $verbose = 0;

    function __construct() 
    {
    }

    function __init() 
    {
        global $config;
        $this->_wpcli = $config->option()[ 'wp-cli' ];
    }

    /**
     * run the command provided
     */
    function do_command( $wpclicmd, $username = null ) {

        if ( null === $this->_wpcli ) {
            $this->__init();
        }

        $cmd = '';
        if ( $username ) {
            $cmd = "sudo -u " . $username . " -- ";
        }
        $cmd .= $this->_wpcli . " " . $wpclicmd;

        if ( $this->verbose ) {
            logger ( "Running: " . $cmd . "\n" );
        }

        $res = trim ( cmd_runner ( $cmd ) );
        return $res;
    }
}

?>