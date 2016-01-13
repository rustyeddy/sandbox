<?php

require_once 'mysql.php';

function cmd_runner ( $cmd, $explode_nl = false )
{
    $out = rtrim ( shell_exec( $cmd ) );
    if ( $explode_nl ) {
    	$out = explode( "\n", $out );
    }
    return $out;
}

function smash_args( $args )
{
    $smashed = array();
    foreach ( $args as $a ) {

        $a = explode( "=", $a );

        if ( count ( $a ) > 1 ) {
            $smashed[ $a[ 0 ] ] = $a[ 1 ];
        }
    }

    return $smashed;
}

/**
 * Got this from stackoverflow: question: 2510434
 */
function format_bytes($bytes, $precision = 2) 
{ 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    
    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 
    
    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

/**
 * TODO: Add the funtionality to write to a log file
 */
function logger( $str )
{
    global $verbose;
    if ( $verbose == 0 ) return;
    
    \cli\out( $str );
}

function logger_r( $foo )
{
    global $verbose;
    if ( $verbose == 0 ) return;
    
    \cli\out( print_r ( $foo ) );
}


function debug( $args )
{
    logger ( "\n\n--------------" );
    logger( $args );
    logger( "--------------\n\n" );

}

function debug_r( $args )
{
    logger( "\n\n--------------" );
    logger_r ( $args );
    logger( "--------------\n\n" );

}

function dumper( $o ) 
{
    global $verbose;
    if ( $verbose == 0 ) return;

    foreach ( $o as $k => $v ) {
        logger( sprintf( "%30s => %s\n", $k, $v ) );
    }
    logger( "\n" );
}

?>