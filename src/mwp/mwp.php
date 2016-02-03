#!/usr/bin/php
<?php

set_include_path( get_include_path() . PATH_SEPARATOR . 'src' );

require_once( 'src/bootstrap.php' );

/*
 * Get options
 * ------------------------------------------------------ */
$opts = "dihs:v";

$lopts = array(
    'debug',                    /* -d */
    'interactive',              /* -i */
    'shutup',                   /* -h = Don't say anything */
    'sites-file',               /* -s sites-file what sites file to use or not */
    'test',                     /* -t used when testing */                             
    'verbose',                  /* -v */
    );

$options = getopt( $opts, $lopts );

/*
 * Setup the command indexes properly
 -------------------------------------------------------------------- */
$cmdidx = 1;
$cmdidx += count ( $options );
$argcount = count ( $argv );
$args = $argv;
$verbose = 1;

/*
 * We will run the command line interpreter if we have no arguments
 * or one of the options 'i or interactive' were provided
 -------------------------------------------------------------------- */

$interactive = ( ( $argcount <= $cmdidx ) || 
        array_key_exists ( 'interactive', $options ) ||
        array_key_exists ( 'i', $options ) ) ?
    true : false;

/*
 * If we have words that come after the command we'll run those
 * through the interpreter.
 *-------------------------------------------------------------------- */

if ( $argcount > $cmdidx ) {

    // The command is the first thing after the options
    $args = array_slice( $argv, $cmdidx );
    do_command ( $args );
}

/*
 * Start running the commands if we are in interactive mode
 -------------------------------------------------------------------- */
if (  $interactive ) {
    $cmd = '';
    while ( $cmd != 'exit' ) {

        $args = do_readline();
        if ( $args === null ) continue;

        do_command( $args );
    }
}

/*
 * Start reading lines from the command line and adding them to
 * our history 
 * -------------------------------------------------------------------- */
function do_readline( $prompt = 'command > ')
{
    if ( function_exists ( 'readline' ) ) {
        $cmd = readline( $prompt );
        if ( $cmd === '' ) return null;
        readline_add_history($cmd);
    } else {
        $cmd = \cli\prompt( "mwp ");
        if ( $cmd === '' ) return null;
    }

    $args = explode( " ", $cmd );
    return $args;
}

/*
 * Actually perform a command
 * -------------------------------------------------------------------- */
function do_command( $args ) 
{
    global $commands;

    $cmdstr = $args[0];
    if ( ! array_key_exists ( $cmdstr, $commands ) ) {
        logger( "Hmmm, I don't know what $cmdstr means..., try one of these instead:\n\n" );
        $cmd = $commands[ 'help' ];
    } else {
        $cmd = $commands[ $cmdstr ];
    }

    $ret = $cmd->do_me( $args );
    return;
}

