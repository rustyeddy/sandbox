<?php

require_once 'command.php';
require_once 'utils.php';

/**
 * We will treat the special database 'mysql' as the grandaddy that
 * we'll use to administer all the other databases.
 */
class MongoMWP extends Command
{
	private $_connection;
	private $_dbname 			= 'mwp';	// By default
	private $_db;				

    public $helptext = "Mongo Database";
    public $helpcommands = array(
    	'mongo config',
        'mongo options',
    );

    private function _add_commands()
    {
        $this->_commands = array(
            'config'    => 'List the configuration on this computer',
            'options'	=> 'List the options table',
        );
    }

    public function __construct()
    {
    	$this->_connection = new MongoClient();

    	$dbname = $this->_dbname;
    	$this->_db = $this->_connection->$dbname;
    }

    public function save( $col, $item ) 
    {
        $col = $this->_db->$col;
        $ret = $col->save( $item );
        return $ret;
    }

    public function get_collection( $col, $add = null, $mid = null )
    {
    	$col = $this->_db->$col;
        if ( $col->count() == 0 ) {
        	if ( null !== $add ) {

            	// We need to init the options collection
        		echo "We don't have $col options in our db, putting it in ...\n";

        		// Add an _id if we were given one
        		if ( null !== $mid ) {
        			$add[ '_id' ] = $mid;
        		}

        		try {

        			$res = $col->insert( $add );

        		} catch ( MongoCursorException $e ) {
        			echo "Can't save the collection: $c: " . $e->getMessage() . "\n";
        			return null;
        		}

        		$col = $this->_db->$col;
        	} else {
        		$col = null;
        	}
        }
    	return $col;
    }

    public function insert_or_update( $args ) 
    {
        die ('TODO: implement mongo :: insert_or_update()');
    }
}
