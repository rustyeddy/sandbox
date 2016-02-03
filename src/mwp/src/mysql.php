<?php

require_once 'command.php';
require_once 'utils.php';

/**
 * We will treat the special database 'mysql' as the grandaddy that
 * we'll use to administer all the other databases.
 */
class MySQL extends Command
{
    public $helptext = "Manage Databases";
    public $helpcommands = array(
        'mysql list',
        'mysql create dbname=DBNAME [dbuser=USER] [dbpasswd=PASSWD] [dbhost=HOST]',
        'mysql delete DBNAME',
        'mysql admin [dbname=mysql] [dbuser=USER] [dbpasswd=PASSWD] [dbhost=localhost]',
    );

    private $_mysql_admin = array(
        'user'      => null,
        'passwd'    => null,
        'database'  => 'mysql',
        'host'      => 'localhost',
        );

    private $_databases = array();


    private function _add_commands()
    {
        $this->_commands = array(
            'list'      => 'List the databases on this server',
            'create'    => 'Create a new database',
            'delete'    => 'Delete a database',
            'admin'     => 'set admin PRIVILEGES',
        );
    }

    /**
     * Create a connection to the primary mysql database on this
     * server, this allows us to access all the the WP databases on
     * our server.
     */
    public function get_mysql()
    {
        // Get the admin creds
        $this->cmd_admin();
        $admuser    = $this->_mysql_admin[ 'user' ];
        $admpass    = $this->_mysql_admin[ 'passwd' ];

        if ( ! array_key_exists ( 'mysql', $this->_databases ) ) {

            // Open the mysql database
            $dbh = new Database( 'mysql', $admuser, $admpass );
            $this->_databases[ 'mysql' ] = $dbh;

        } else {
            $dbh = $this->_databases[ 'mysql' ];
        }

        if ( ! $dbh->is_connected() ) {
            $dbh->connect();
        }

        return $dbh;
    }

    /**
     * Get a list of database names on this server
     */
    public function get_list()
    {
        $dbh = $this->get_mysql();
        $dbnames = array();

        $q = $dbh->query( 'show databases' );
        $dbs = $q->fetchAll();
        foreach ( $dbs as $db ) {
            $dbnames[] = $db[ 'Database' ];
        }
        return $dbnames;
    }

    /**
     * List all the databases that exist on this server.  This will
     * require use to open that database.
     */
    public function cmd_list()
    {
        $dbs = $this->get_list();
        foreach ( $dbs as $db ) {
            logger ("\t" . $db . "\n");
        }
        return true;
    }

    public function create( $dbname, $dbuser, $dbpasswd, $dbhost = 'localhost' )
    {
        $mysql = $this->get_mysql();
        if ( $mysql->dbh === null ) {
            return null;
        }

    	$cmd = "CREATE DATABASE " . $dbname . ';';
    	$cmd .= sprintf ("GRANT ALL PRIVILEGES ON %s.* TO '%s'@'localhost' identified by '%s';", 
    		$dbname, $dbuser, $dbpasswd );

    	$cmd .= "FLUSH PRIVILEGES;";
    	$ret = $mysql->dbh->exec($cmd) or die( print_r( $mysql->dbh->errorInfo(), true ) );
        return $ret;
    }

    /**
     * This is broken
     */
    public function cmd_create( $args = null )
    {
        logger ( "Creating a database: ");
        $mysql = $this->get_mysql();

        /*
         * XXX - This assumes that we are using the same username and
         * password as mysql.  We really ought to change that!
         */
        $smashed = smash_args( $args );
        if ( $smashed == null || ! array_key_exists( 'dbname', $smashed ) ) {
            logger ("ERROR: you did not give us a database name! use: 'dbname=wp_something'\n");
            return null;
        }

        $dbname = $smashed[ 'dbname' ];

        logger ( $dbname . "... " );
        $ret = $this->create( $dbname, $mysql->dbuser, $mysql->dbpasswd, $mysql->dbhost );

        logger ( "done!\n" );
        return $ret;
    }

    public function delete( $dbname )
    {
        $sql = "drop database if exists $dbname";
        $mysql = $this->get_mysql();
        $ret = $mysql->dbh->exec( $sql );

        return $ret;
    }

    public function cmd_delete( $args )
    {
        $dbname = $args[2];
        $this->delete( $dbname );
    }

    // get the admin credentials
    public function get_admin( $host = 'localhost' )
    {
        global $mongo;

        $this->_mysql_admin[ 'host' ] = $host;
        $col = $mongo->get_mysql( 'mysql', $this->_msyql_admin );
        return $col;
    }

    /**
     *  Break these into get, update and insert to avoid confusion?
     */
    public function cmd_admin( $args = null ) 
    {
        global $mongo;
        $update = false;

        $admin = $this->_mysql_admin;
        $col = $mongo->get_collection( 'mysql', $admin, $admin[ 'host' ] );
        $adm = $col->findOne();

        dumper( $adm );
        $this->_mysql_admin = $adm;

        if ( $args == null || count( $args ) < 3 ) {
            return $adm;
        }

        $admin = array();
        // If we have extra args we are going to update the config DB
        foreach ( $args as $a ) {
            if ( $a === 'mwp' ||  $a === 'mysql' || $a === 'admin' ) continue;

            $f = explode( '=', $a );
            $admin[ $f[0] ] = $f[1];
            $update = true;
        }

        if ( ! array_key_exists('host', $admin) ) {
            $admin[ 'host' ] = 'localhsot';
        }

        $newadm = null;
        // If $args count is > 2 we are updating something
        if ( count( $args ) > 2 ) {
            print "Is being changed to ...\n\n";
            $newadm = $col->findAndModify(
                array( '_id'    => $adm[ '_id'] ),
                array( '$set'   => $admin ),
                null,
                array( 'new'    => true)
                );

            $this->_mysql_admin = $newadm;
            dumper( $newadm );
        }

        return $newadm;
    }
}

class Database
{
    public $dsn;
    public $dbh = null;

    public $dbname;
    public $dbuser;
    public $dbpasswd;
    public $dbhost;

    public function __construct( $dbname, $user, $passwd, $host = 'localhost' )
    {
    	$this->dbname = $dbname;
    	$this->dbuser = $user;
    	$this->dbpasswd = $passwd;
    	$this->dbhost = $host;

    	$this->dsn = 'mysql:host=' . $host;
    	if ( $dbname != null ) {
    		$this->dsn .= ";dbname=" . $dbname;
    	}
	}

    public function connect()
    {
    	try {

    		$this->dbh = new PDO( $this->dsn, $this->dbuser, $this->dbpasswd );

    	} catch ( PDOException $e ) {
    		logger ("Ah hell: " . $e->getMessage() . "\n");
    	}
    	return $this->dbh;
    }

    public function disconnect()
    {
    	$this->dbh = null;
    }

    public function getdbh()
    {
    	if ( NULL === $this->dbh ) {
    		$this->connect();
    	}
    	return $this->dbh;
    }

    public function is_connected()
    {
    	return ($this->dbh !== null) ? true : false ;
    }

    /*
     * Drop the tables: Yikes! this is dangerous!!! Don't really do it.
     */
    public function drop_tables ( )
    {
    	$stmt = $this->dbh->query( "show tables" );
    	$tabs = $stmt->fetchAll();
    	foreach ( $tabs as $tab ) {
    		$ret = $this->dbh->exec( 'drop table if exists ' . $tab[0] );
    		logger ( "Dropping table " . $tab[0] . " returned " . $ret . "\n");
    	}
    }

    public function get_tables( ) 
    {
    	$stmt = $this->dbh->query( "show tables" );
    	$tables = $stmt->fetchAll();
	
    	return $tables;
    }

    public function query( $sql )
    {
    	logger( "Running: $sql\n" );

    	$q = $this->dbh->query( $sql );
    	if ( $q == null ) {
    		$err = $this->dbh->errorInfo();
    		logger( "FAIL: " . $sql . " " . $err[2] );
    	}

    	return $q;
    }

    public function insert_many( $table, $values )
    {
    	$s = $values[0];
    	foreach ( $s as $f => $v ) {
    		$ins[] = ':' . $f;
    	}
	
    	$ins = implode(',', $ins);
    	$fields = implode(',', array_keys($s));
    	$sql = "INSERT INTO $table ($fields) VALUES ($ins)";
	
    	logger( $sql . "\n");

    	$lastId = $this->insert_prepare( $sql, $values );
	
    	logger( "DB insert complete - returned " . $lastId . "\n");
    	return $lastId;
    }

    public function insert_prepare( $sql, $values )
    {
    	$sth = $this->dbh->prepare($sql);
    	foreach ( $values as $s ) {
    		foreach ( $s as $f => $v ) {
    			$sth->bindValue(':' . $f, $v);
    		}
    		$sth->execute();	/* XXX - Error checking needed */
    	}
    	$lastId = $this->dbh->lastInsertId();
    	return $lastid;
    }
}

?>
