<?php

/**
 * This is the Command base class to be extended by the reset of the
 * commands that we are going to be loading.
 */
class Command
{
    public $name = '';
    public $helptext = 'XXX - Need to add help!';
    public $helpcommands = array( "XXX - need to add help commands" );
	public $_commands = array();

    /**
     * Self register with the $commands global array
     */
    public function __construct()
    {
        global $commands;

        if ( $this->name === '' ) {
            $this->name = strtolower( get_class( $this ) );
        }

        /* Does this need to be by reference? */
        $commands[ $this->name ] = &$this;
        $this->_add_commands();
    }

    /**
     * Default is to not add any sub commands
     */
    private function _add_commands()
    {
        /*
         * Add commands as key value pairs: to the $this->_commands
         * array cmdname => helptext,
         *
         * for  example:
         *
         *    $this->_commands[ "list" ] => "List all the items of this command";
         */
    }

    /*
     * Default help printer for this command
     */
    public function help( $args = null )
    {
        $txt = '';
        if ( null === $args ) {
            $txt = sprintf ( "%10s: %s\n", $this->name, $this->helptext );
        } else {

            $cmd = null;
            if ( count ( $args ) > 1 ) {
                global $commands;

                /*
                 * Here we are checking for the 1st level command
                 */
                if ( array_key_exists( $args[ 1 ], $commands ) ) {

                    $cmd = $args[ 1 ];
                    $txt .= $this->help_commands();

                } else {

                    $txt .= "Hmmm, I don't understand: " . implode( ' ', $args );
                    $txt .= "\n\n";
                }

            }
            if ( null === $cmd ) {
                $txt .= $this->help_commands();
            }
        }
        return $txt;
    }

    public function help_commands( $args = '' )
    {
        $txt = implode( "\t\n", $this->helpcommands );
        return $txt . "\n\n";
    }
            
    public function do_me( $args = null )
    {
        $results = '';
        if ( $args !== null ) {

            $cmd = $args[0];
            $subcmd = null;
            if ( count ( $args ) > 1 ) {
                $subcmd = 'cmd_' . $args[1];
            }

            /**
             * If the caller asked for a command that does not exist, let them
             * know, then print out a list of commands that we do know.
             */
            if ( $subcmd != null ) {
                if ( method_exists ( $this, $subcmd ) ) {

                    $results = $this->$subcmd( $args );

                } else {
                    // Turn these into logger commands
                    logger ( $cmd . " hmmm. I don't know command: $cmd ... \n");
                    logger ( $this->help_commands() );

                }
            }
        }
        
        return $results;
    }
}

/**
 * If the help command was called with only one argument, that is
 * 'help' alone, then we'll give the user a dump of all the first
 * level commands that are registered.
 *
 * If the user provides two or more arguments, those help commands are
 * going to be passed along to the command for processing.
 */
class HelpCmd extends Command
{
    public $name = 'help';
    public $helptext = "what you are reading now";

    function do_me( $args = '' )
    {
        global $commands;

        $helptxt = '';

        /*
         * Are we going to handle the high level command or give a
         * more specific help.
         */
        if ( count ( $args ) > 1 ) {

            if ( ! array_key_exists ( $args[ 1 ], $commands ) ) {

                logger( "Hmm, I don't know how to help you with: " . $args[ 1 ] . "\n\n" );
                return;

            }

            $cmd = $commands[ $args[ 1 ] ];
            $helptxt = $cmd->help ( $args );

        } else {

            foreach ( array_keys ( $commands ) as $key ) {

                $helptxt .= $commands[ $key ]->help();
                
            }

            $helptxt .= "\n";

        }

        logger( $helptxt );
        return $helptxt;
    }
}

class ExitCmd extends Command
{
    public $name = 'exit';
    public $helptext = "Exit mwp";

    /**
     * Leave. This will be a good place to save any data that we may
     * have wanted to save before the next session, if it comes to
     * that.
     */ 
    function do_me( $args = null )
    {
        logger ( "Goodbye, cruel world!\n" );
        exit();
    }
}

?>