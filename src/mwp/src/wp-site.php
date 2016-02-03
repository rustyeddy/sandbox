<?php

require_once 'backupbuddy.php';

$site_doc = array(
    // WP location on drive and internet
    'server'        => '',
    'siteurl'       => '',
    'wpdir'         => '',
    'alias'         => '',

    // Unix account info
    'username'      => '',
    'homedir'       => '',
    'uid'           => '',
    'gid'           => ''
    );

class wp_site 
{
    public $server;

    // WP Site info
    public $siteurl;
    public $wpdir;
    public $alias;

    // Database info
    public $dbinfo;

    /**
     * Account info
     */
    public $username;
    public $homedir;
    public $uid;
    public $gid;

    private $_popdir;

    public $backupbuddy;

    function __construct( $o = '' ) 
    {
        if ( $o !== '' ) {
            $this->wpdir = $o;
        }
        $this->grok_uid();
        $this->backupbuddy = new BackupBuddy( $this );
    }

    /**
     * Restore this site cd .from the MongoDB.
     */
    public function from_mongo( $m ) 
    {
        $this->from_array( $m );    // Will this work?
    }

    function mongo_save()
    {
        global $site_doc;
        global $mongo;

        $site = array();
        foreach ( $site_doc as $k => $v ) {

            if ( property_exists ( $this, $k ) ) {
                $site[ $k ] = $this->$k;
            } else {
                print "property does not eixst: $k\n";
                $site[ $k ] = '';
            }
        }

        $site[ '_id' ] = $this->siteurl;
        $mongo->save( 'sites', $site );
    }

    /**
     * Restore this site from a JSON array.  This function is used
     * after reading the stored file from the disk.
     */
    public function from_array( $a )
    {
        foreach ( $a as $k => $v ) {
            if ( is_array( $v ) ) {
                if ( $k == 'dbinfo' ) {
                    $this->dbinfo = $v;
                }
            }

            $this->$k = $v;
        }
    }

    /**
     * Figure out the user information for this install
     * 
     * TODO: create an account class to hold this information, 
     * create an account global to store this info in!
     */
    function grok_uid()
    {
        $paths = explode( '/', $this->wpdir );

        /** 
         * XXX - Create an account class and store all of this information
         * in the account class.
         */
        $userinfo = posix_getpwuid( fileowner( $this->wpdir ) );
        $pwname = posix_getpwnam( $this->username );

        $this->username = $userinfo[ 'name' ];
        $this->uid = $pwname['uid'];
        $this->gid = $pwname['gid'];
        $this->homedir = $pwname['dir'];
    }

    /** 
     * public check dir and change
     */
    public function check_and_change_dir()
    {
        if ( getcwd() != $this->wpdir ) {
            $this->pushd();
        }
    }

    /**
     * change directories to this sites WP dir
     */
    public function pushd()
    {
        if ( ! is_dir( $this->wpdir ) ) {
            logger ("Whoops this WP directory does not exist: " . $this->wpdir . "\n" );
            return false;
        }

        $this->_popdir = getcwd();
        $ret = chdir ( $this->wpdir );
        if ( $ret == false ) {
            logger ("Crap can't change to my own directory: " . $wp->wpdir . "\n");
        }
        return $ret;
    }

    /**
     * Pop back to previous directory
     */
    public function popd()
    {
        $ret = chdir ( $this->_popdir );
        if ( $ret == false ) {
            logger ("Crap could not pop the dir: " . $wp->_popdir . "\n");
        }
        return $ret;
    }

    function display()
    {
        logger ( sprintf ( "%-40s %s\n", $this->siteurl, $this->wpdir) );
    }

    /**
     * Get the db info for this WP site.
     *
     * NOTICE: We must already be in this sites wpdir
     */
    public function get_dbinfo()
    {
        $wpconfig = 'wp-config.php';

        $res = preg_grep( '/(DB_\S+), (\S+)/', file($wpconfig) );
        $dbinfo = null;
        foreach ( $res as $dbstr ) {
            $res = preg_match( '/\'(\S+)\'\,\s*\'(\S*)\'/', $dbstr, $matches );
            if ( $res ) {
                $dbinfo[ $matches[ 1 ] ] = $matches[ 2 ];
            }
        }
        $this->dbinfo = $dbinfo;
        return $dbinfo;
    }

    /**
     * Get the siteurl.
     *
     * Call wp cli if we have to.
     */
    public function get_siteurl()
    {
        if ( ! $this->siteurl ) {
            global $wp;

            $this->siteurl = $wp->do_command( "option get siteurl", 
                $this->username );
        }
        return $this->siteurl;
    }


    /** 
     * Get the plugin list for this site.
     * 
     * XXX: Need to fix this.
     */
    function plugin_list( $flags = '' )
    {
        global $wp;

        $verbose = false;
        $updates = false;

        if ( ! $this->pushd() ) {
            return;
        }

        $cmd = 'plugin list --format=json';

        $json = cmd_runner ( $cmd );
        $plugins = json_decode( $json );

        foreach ( $plugins as $p ) {
            if ( $p->update == 'available' ) {
                $updates = "updates available";
            }
            if ( $verbose == 2 ) {
                logger ( sprintf( "%40s %9s %20s %12s\n",
                        $p->name, $p->status, $p->update, $p->version) );
            }
        }

        if ( $updates ) {
            logger (sprintf( "%40s updates available\n", $this->siteurl) );
        } else {		
            if ( $verbose ) {
                // XXX this is broke, look at the $updates variable
                logger ( sprintf( "%40s %s\n", $this->siteurl, $updates ) );
            }
        }

        if ( ! $this->popd() ) {
            return;
        }
    }

    /**
     * Update this sites plugins
     * 
     * XXX Need to udpate this.
     */
    function plugin_update()
    {
        if ( ! $this->sudo_pushd() ) return;

        $cmd = 'wp plugin update --all';
        $out = cmd_runner ( $cmd );
        logger( "Updating plugins for " . $this->siteurl . " " . $out . "\n");

        /* This will work for sure, right? */
        if ( ! $this->unsudo_popd() ) {
            logger ("Aw man, could not unsudo: " . $this->siteurl . "\n");
        }
    }

    function theme_list()
    {
        $verbose = false;
        $updates = 'up to date';

        if ( ! $this->pushd() ) {
            return;
        }
        $cmd = 'wp theme list --format=json';

        $json = cmd_runner ( $cmd );
        $plugins = json_decode( $json );

        foreach ( $plugins as $p ) {
            if ( $p->update == 'available' ) {
                $updates = "updates available";

                logger ( sprintf( "%40s %9s %20s %12s\n",
                        $p->name, $p->status, $p->update, $p->version) );
            }
        }
        if ( ! $this->popd() ) {
            return;
        }
    }

    /** -------------------------------------------------------------
     * Get backup summary
     */
    public function get_backup_summary()
    {
        $bs = $this->backupbuddy->get_backup_summary();
        return $bs;
    }

    /**
     * Print out info for the site, eventually will add some args
     * controlling how much info to print.
     */
    public function printer( $opts = null )
    {
        $fmt = "%-30s %-20s ";

        $args[] = $this->siteurl;
        $args[] = $this->wpdir;

        if ( $this->dbinfo[ 'DB_NAME' ] ) {
            $fmt .= " %-15s ";
            $args[] = $this->dbinfo[ 'DB_NAME' ];
        }
  
        $bs = null;
        if ( $this->backupbuddy ) {
            $bs = $this->backupbuddy->get_backup_summary();
        }

        if ( $bs ) {
            $fmt .= "%4s %10s %10s "; 
            $args[] = $bs[ 'count' ];
            $args[] = $bs[ 'totalsize' ];
            $args[] = $bs[ 'lastfull' ];
        }
        logger ( vsprintf ( $fmt . "\n", $args ) );
    }
}

?>