<?php

require_once 'wp-cli.php';

/*
 * Find WP install directories
 */
class WP_Sites extends Command
{
    public $name = 'sites';
    public $helptext = "List websites we manage";
    public $helpcommands = array(
        'sites',
        'sites scan',
        'sites backups',
        'sites fetch',
    );

    private $mwproot;
    private $sitesroot;
    private $wpsites = array();
    private $ignoredirs = null;
    private $_inited = false;

    private $findcmd;

    function __construct()
    {
        global $config;         /* XXX can't find it */

        $opts = $config->option();

        // Save the root of this util when we need to go back
        $this->mwproot = getcwd();
        $this->sitesroot = $opts[ 'sites-root' ];

        // Look for directories with wp-content
        $this->findcmd = 'find ' . $this->sitesroot . ' -type d -name wp-content';
        parent::__construct();
    }

    private function _add_commands()
    {
        $this->_commands = array(
            'sites'     => 'scan this host for sites',
            'plugins'   => 'run the wp command on all sites',
            'backups'   => 'scan for backups',
        );
    }

    /**
     * Run the commands
     */
    public function do_me ( $args = null )
    {
        global $verbose;

        if ( $args === null || count ( $args ) < 2 ) {
            $this->cmd_list( null );
            return;
        }

        switch ( $args[1] ) {

        case 'list':
            $sites = $this->cmd_list();
            break;

        case 'scan':
            $sites = $this->cmd_search();
            break;

        case 'backups':
            $this->cmd_backups();
            break;

        case 'fetch':
            $this->fetch_sites();
            break;

        default:
            echo "I don't understand: " . $args[1] . "\n";
            break;
        }
    }

    /**
     * Do a full on search on the filesystem for WordPress installs
     */
    public function cmd_search( $savesites = true )
    {
        global $wp;

        logger( "Looking for WP installs with: `". $this->findcmd . "`\n");

        $sarray = array();

        $alldirs = cmd_runner( $this->findcmd, true );
        foreach ( $alldirs as $dir ) {

            if ( strpos( $dir, 'total-cache' ) ) continue;
            if ( $this->ignoredirs && in_array( $dir, $this->ignoredirs ) ) continue;

            $wpdir = dirname( $dir );
            if ( strlen ( $wpdir ) < 1 ) {
                continue;
            }

            $site = new wp_site( $wpdir );
            $this->wpsites[] = $site;

            $site->pushd();             // Change to the site directory
            $site->get_dbinfo();        // Get the site db info.
            $site->get_siteurl();

            // Now get information about the backups
            $site->get_backup_summary();
            $site->printer();

            if ( $savesites ) {
                $site->mongo_save();
            }
        }

        return $sarray;
    }

    /**
     * Read the sites from MongoDB
     */
    function read_sites()
    {
        global $mongo;

        $col = $mongo->get_collection( 'sites' );
        $cur = $col->find();
        foreach ( $cur as $id => $s ) {

            /**
             * XXX - Todo write a from_array function to repopulate the database
             */
            $site = new wp_site( );
            $site->from_mongo( $s );
            $this->wpsites[] = $site;
        }
        return count( $this->wpsites );
        print_r( $this->wpsites );
    }

    /**
     * This function will return the sites according to:
     * 
     * 1. Cached list of sites
     * 2. Read from the sites file
     * 3. Scan the filesystem for websites
     */
    public function get_sites()
    {
        $cnt = 0;
        if ( $this->wpsites === null || count ( $this->wpsites ) == 0 ) { 
            $cnt = $this->read_sites();
        }
        
        $cnt = count( $this->wpsites );
        if ( $cnt === 0 ) {
            $sites = $this->cmd_search( true );
        }
        return $this->wpsites;
    }

    /**
     * Do a quick scan.
     *
     * TODO: quickly run through the list to see if anything on disk
     * has changed.  We can also call the database tables to see what
     * if anything there has changed.
     */
    public function cmd_list( $args = null )
    {
        $sites = $this->get_sites();
        foreach ( $sites as $s ) {
            $s->printer();
        }
        logger( "\n" );
    }

    /**
     * Search for backups on this server
     */
    public function cmd_backups()
    {
        $sites = $this->get_sites();

        $backup_summaries = array();

        logger ( sprintf ( "%-30s %-5s %5s\n", "url", "count", "size or info" ) );
        logger ( sprintf ( "%-30s %-5s %5s\n", "----------------------------", "-----", "------------" ) );

        foreach ( $sites as $site ) {
            $bs = $site->get_backup_summary();

            $size = ( $bs[ 'totalsize' ] == 0 ) ? "0 MB" : $bs[ 'totalsize' ];
            $txt = sprintf( "%-30s %3d %10s %11s\n",
                $site->siteurl, $bs[ 'count' ], $size, $bs[ 'lastfull' ]);

            logger ( $txt );
        }
        logger ( "\n" );
        return $backup_summaries;
    }

    /**
     * Fetch sites from mom
     * 
     * @url GET /sites
     */
    function sites()
    {
        $url = 'http://mom.gumsole.com/wp-json/';
        $args = 'posts?type[]=site';

        $url .= $args;

        $json = file_get_contents( $url );
        $sites = json_decode( $json );

        $json = json_encode($sites);
        return $json;
    }

    /*
     * XXX Not yet...
     *
    function list_wp_sites()
    {
        foreach ( $this->wpsites as $s ) {
            $s->display();
        }
    }

    function plugin_list()
    {
        foreach ( $this->wpsites as $s ) {
            $s->plugin_list();
        }
    }

    function plugin_update()
    {
        foreach ( $this->wpsites as $s ) {
            $s->plugin_update();
        }
    }

    function run_cmd ( $cmd, $args )
    {
        foreach ( $this->wpsites as $s ) {
            $s->run_cmd();
        }
    }

    */
}
