<?php

require_once 'command.php';

/**
 * Read set the configuration file.
 */
class Configurator extends Command
{
    public $name = 'config';
	public $helptext = "read and set configuration items: see config-sample.json";
    public $helpcommands = array(
        'config option', "config option [[item] value] - Dump or set the option(s)",
    );

    private $options = array(
        'verbose'           => false,
        'sites-root'        => '/www/html', 
        'mwp-root'          => '/home/rusty/src/mwp',
        'apache_config_dir' => '/etc/apache2',
        'wp-cli'            => '/home/rusty/src/wp-cli/bin/wp',
        );

    private $_configuration; 
    public function __construct()
    {
        global $verbose;

        parent::__construct();
        $this->_add_commands();
        $verbose = $this->option( 'verbose' );
    }

    private function _add_commands()
    {
        $this->_commands = array(
            'option'   => "config option [[item] value] - Dump or set the option(s)",
        );
    }

    public function cmd_option( $args = null ) 
    {
        $cnt = ( null === $args ) ? 0 : count( $args );
        $opts = $this->option();
        if ( $opts == null ) {
            logger( "Crap, something when wrong!\n");
            return -1;
        }

        switch ( $cnt ) {
            case 1:
            case 2:
            case 3:
            default:
            dumper ( $opts );
            break;

            case 4:
            $opt = $this->option( $args[2], $args[3] );
            logger ( sprintf( "%30s => %s\n", $args[ 2 ], $opt[ $args[ 2 ] ] ) );
            break;
        }
        return 0;
    }

    // If val equal null just return the existing value
    public function option( $item = null, $newval = null ) 
    {
        global $mongo;
        $val = null;

        // Add some caching ???
        $col = $mongo->get_collection( 'options',  $this->options );
        $opts = $col->findOne();      // There should only ever be one!
        if ( $opts === null ) {
            return null;
        }

        if ( $item == null || $newval == null ) {
            return $opts;
        }

        $newopts = $col->findAndModify(
            array( '_id'    => $opts[ '_id'] ),
            array( '$set'   => array( $item => $newval ) ),
            null,
            array( 'new'    => true)
            );

        if ( $item === 'verbose' ) {
            global $verbose;
            $verbose = 1;
        }

        // TODO: Insert code to update the options document
        return $newopts;
    }
}

?>