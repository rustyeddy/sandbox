<?php

require_once 'lib/html.php';
require_once 'vendor/php-markdown/Michelf/Markdown.inc.php';
//require_once 'vendor/php-markdown/Michelf/MarkdownExtra.php';

function concat_path($r, $f)
{
    $p = $r . DIRECTORY_SEPARATOR . $f;
    return $p;
}

class PageMaker
{
    /* config sets */
    public $rootdir     = '.';
    public $template    = 'home.html';
    public $stylesheets = array();

    /* The current read */
    public $relative_dir = null;    /* the directory we currently in */
    public $directories = array();
    public $files       = array();

    public $breadcrumbs = array();

    /* The processed HTML (or not) content */
    public $content     = '';       

    function __construct( $d, $t, $s = '' )
    {
        $this->rootdir   = realpath( $d );
        $this->template = $t;

        if ( $s && $s !== '' ) {
            $this->stylesheets[]  = $s;
        }
    }

    function page_title()
    {
        $html = '<title>Scratch Pad</title>';
        return $html;
    }

    /**
     * get stylesheets
     */
    function stylesheets( )
    {
        $html = '';

        foreach ( $this->stylesheets as $s ) {
            $html .= "<link rel='stylesheet' href='".
                $s . "' type='text/css' />\n";
        }
        return $html;
    }

    function home()
    {
        $html = href( "Home", 'http:' );
        return $html;
    }

    /**
     * Create some bread crumbs
     */
    function breadcrumbs()
    {
        $dirs = explode('\\', $this->relative_dir);

        $p = '';

        $html = '<ul class="bread-crumb">';

        $html .= '  <li class="bread-crumb">';
        $html .= $this->home();
        $html .= '  </li>';

        foreach ( $dirs as $d ) {

            $p .= '/' . $d;
            $this->breadcrumbs[$d] = $p;

            $link = 'http:?d=' . $p;

            $html .= "<li class='bread-crumb'>";
            $html .= ' / ' . href( $d, $link );
            $html .= "</li>";
        }

        $html .= "</ul>";

        return $html;
    }

    /**
     * Read the directory and sort the results into the
     * directories and files arrays.
     */
    function read_directory( $d = null )
    {
        $fullpath = $this->rootdir;

        /*
         * if we have a relative dir, append that to the
         * full path
         */
        if ( $d ) {

            $fullpath = concat_path ( $fullpath, $d );
            $this->relative_dir = $d;

        }

        // Start reading the directory
        $dh = opendir( $fullpath );
        if ($dh !== null) {

            while (false !== ( $e = readdir($dh) )) {

                // Ignore the current and parent directories~
                if ($e === '.' || $e === '..') {
                    continue;
                }

                /*
                 * Ignore files that end in a "~".
                 *
                 * Todo: This can and probably should be made more
                 * generic
                 */
                if ( substr( $e, -1 ) === '~' ||
                     substr( $e, 0 ) === '#' ) {
                    continue;
                }

                $relpath        = concat_path( $d, $e );
                $full           = concat_path( $fullpath, $e );
                
                if (is_dir( $full )) {

                    $this->directories[] = $e;

                } else if ( is_file ( $full ) ) {

                    $this->files[] = $e;

                }
            }
        
            closedir ($dh);
        }
    }

    /**
     * Process the file
     */
    function process_file( $f )
    {
        $fullpath = concat_path (
            concat_path ($this->rootdir, $this->relative_dir),
            $f );

        $parts = pathinfo( $fullpath );

        $fp = fopen( $fullpath, 'r');
        if ( $fp === null ) {
            $this->content = "Fail: Could not open the file: " . $full . "<br/>\n";
        }

        $data = fread($fp, filesize( $fullpath ));

        /*
         * Run the raw file through our meta data preprocessor
         */
        $this->process_meta( $data );

        switch ( $parts['extension'] ) {
        case 'md':
            $this->content = \Michelf\Markdown::defaultTransform( $data );
            break;

        default:
            $this->conent = $data;
            break;
        }

        return $this->content;
    }
    

    /** 
     * Meta data (if any) is kept in an HTML comment, preferably
     * in the header of a given page and applies to that page
     */
    function process_meta( $data )
    {
        $start = '[[';
        $end = ']]';

        /*
         * Grab the hunk of text between the start and end
         * meta strings
         */
        $spos = stripos( $data, $start ) + strlen( $start );
        $epos = stripos( $data, $end );
        $metastr = substr( $data, $spos, $epos - $spos );

	$meta = explode('\n\r', $metastr ); /* This is certainly broken! */

	foreach ( $meta as $line ) {
	    $line = trim ( $line );

	    if ( $line == '' || $line == null ) continue;
	    $foo = explode( ':', $line );

	    /* this will likely move to it's own area */
	    switch ( $foo[0] ) {

	    case 'stylesheet':
		$this->stylesheets[] = $foo[1];
		break;

	    default:
		echo "I don't understand: $line <br/>";
		break;
	    }
	}
	
        if ( array_key_exists ('stylesheets', $meta ) ) {
	    
            $sheets = explode(',', $meta['stylesheets'] );
            foreach ( $sheets as $s ) {
                $this->stylesheets[] = $s;
            }
        }
    }
	

    /**
     * Create a navbar from the directories and files
     */
    function navbar_dirs()
    {
        $html = "<nav class='dynamic-nav-dirs'>\n";
        $html .= "  <ul class='dynamic-nav-dirs'>\n";
        foreach ($this->directories as $d) {

            $html .= "    <li class='nav-item'>\n";
            $html .= href($d, '?d=' . concat_path ($this->relative_dir, $d) );
            $html .= "    </li>\n";

        }
        $html .= "</ul> <!-- dynamic-nav-dirs -->\n";
        $html .= "</nav> <!-- dynamic-nav-dirs -->\n";
        return $html;
    }

    /**
     * Create a navbar with the files from the current directory
     */
    function navbar_files()
    {
        if (count ($this->files) <= 0) {
            return '';
        }
        
        $html = "<nav class='dynamic-nav-files'>\n";
        $html .= "<ul class='dynamic-nav-files'>\n";

        foreach ($this->files as $f) {
            $html .= "<li class='nav-item'>\n";

            $arg = '';
            if ( $this->relative_dir ) {
                $arg = '?d=' . $this->relative_dir;
                $arg .= '&f=' . $f;
            } else {
                $arg .= '?f=' . $f;
            }

            $html .= href( $f, $arg );
            $html .= "</li>\n";
        }

        $html .= "</ul> <!-- dynamic-nav-files -->\n";
        $html .= "</nav> <!-- dynamic-nav-files -->\n";
        return $html;
    }

    
    function content()
    {
        if ( $this->content === '' ) {

            // This can be much more efficient !!
            $this->content = "<h2>Files</h2>\n" . $this->navbar_files();
            $this->content .= "<h2>Dirs</h2>\n" . $this->navbar_dirs();
        }
        return $this->content;
    }

}

?>
