#!/usr/bin/python

import logging
from mininet.log import info, LOGMSGFORMAT, lg, LEVELS
import time

# Use mininet logger and create file handler which logs all messages to file
"Define MfwdTestLogger which to log messages to a file"
def addFileHandlerToMininetLogger( level ):
    filename = 'mfwdTest_' + time.strftime( "%Y%m%d-%H%S" ) + ".log"
    fh = logging.FileHandler( './logs/%s' % filename, mode='w' )
    fh.setLevel( LEVELS['%s' % level] )
    fileFormatter = logging.Formatter( LOGMSGFORMAT )
    fh.setFormatter( fileFormatter )
    # Add this handler to Mininet Logger
    lg.addHandler( fh )
    info( '\nLogging to file %s with level %s\n' % ( filename, level ) )
