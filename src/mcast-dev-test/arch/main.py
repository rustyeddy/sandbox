#!/usr/bin/python

import time
import sys
import argparse
from topos.fourswmesh import FourSwMeshTopo
from mininet.net import Mininet
from mininet.node import OVSSwitch, RemoteController
from mininet.cli import CLI
from mininet.util import pmonitor
from time import sleep
from mininet.log import setLogLevel, error, debug, info, output
from utils.util import findHostAndIfaceFromIp, getMininetSNameFromOnosUri
from utils.util import getIpStripNetmask, findNeighborFromSwitchPort
from utils.log import addFileHandlerToMininetLogger
import json
import pexpect

argParser = argparse.ArgumentParser( description='Script for mfwd testing' )

argParser.add_argument( '-num', help='Number of iterations, 0 for continuous',
                        required=False, type=int, default=1 )

argParser.add_argument( '-interval', help='Iteration interval secs',
                        required=False, type=int, default=5 )

argParser.add_argument( '-timeout', help='Timeout in secs', required=False,
                        type=int, default=5 )

argParser.add_argument( '-count', help='Number of packets and 0 for infinity',
                         required=False, type=int, default=10 )

argParser.add_argument( '-input', help='Input JSON file', required=False,
                        type=str, default='test.json' )

argParser.add_argument( '-log', help='Log level to log in file',
                        required=False, type=str, default= 'none' )

argParser.add_argument( '-controller', help='Specific the controller IP address',
                        required=False, type=str, default='127.0.0.1')


argParser.add_argument( '-port', help='Specific the controller port',
                        required=False, type=str, default='8181')

argParser.add_argument( '-docli', help='Provide the CLI after script is complete',
                        required=False, type=bool, default=False)

mainArgs = argParser.parse_args()

# Create the receivers dictionary with group address as key and each grp can
# have multiple receivers
grpReceivers = {}

# Create the Sender dictionary with group address as key and each grp can have
# 1 single sender
grpSender = {}

# It will have a single entry for (S, G) and doesn't allow adding multiple
# entries for (S,G ) and doesn't support (*, G )
# NOTE: This supports a single sender per group and will have to change if
# multiple senders per group have to be supported.

def addSenderOfGrp( grpAddr, senderIp ):
    grpSender[grpAddr] = senderIp

# It will have a single entry for receiver for a particular group and doesn't
# allow adding multiple entries for same receiver and group combination
def addReceiverOfGrp( grpAddr, receiverIp ):
    grpReceivers.setdefault(grpAddr, {})[receiverIp] = 1

# This module will be called when a LEAVE from a host is sent
# def removeReceiverFromGrp(grpAddr, receiverIp):

# def getReceiversOfGrpIfAny(grpIp):
#    return grpReceivers.get(grpIp, {}).keys()

if __name__ == '__main__':

    # Set the log level for Mininet stream logger
    setLogLevel( 'info' )

    # Enable logging of 'output' log level to file
    if ( mainArgs.log is not 'none' ):
        addFileHandlerToMininetLogger( mainArgs.log )

    # Configure OnOs with joins from input JSON file
    jsonFilename = mainArgs.input 
    jsonCurlCmd = 'curl -v -X POST --data @%s -H \
                   "Content-Type:application/json"  \
                   http://%s:%s/onos/mfwd/mcast/join-multicast\r\n' \
                   % ( jsonFilename, mainArgs.controller, mainArgs.port )
    response = pexpect.run( jsonCurlCmd )
    if ( response.find( "Successfully Inserted" ) != -1 ):
        info( "Executed %s" % jsonCurlCmd )
    else:
        error( "Exiting on error %s for cmd %s" % ( response, jsonCurlCmd ) )
        sys.exit()  

    info( "Reading input from %s file\n" % mainArgs.input )
    # Read the mcast configuration from JSON input file
    json_file = open( mainArgs.input, 'r' )
    try:
        mcast_json = json.load( json_file )
    except ( ValueError, KeyError, TypeError ):
        error( "Error from %s failed...exiting\n" % mainArgs.input )
        sys.exit()
    output( "Load from %s is successful\n" % mainArgs.input )

    info( 'Creating mininet topology\n' )
    controller = mainArgs.controller
    "Create network and test multicast packet delivery"
    net = Mininet( topo=FourSwMeshTopo(1), switch=OVSSwitch,
                   controller=lambda a: RemoteController(a,
                                                         ip=controller) )
    net.start()
    output( "\nCreated Mininet topology\n" )

    # Scan the input json to populate senders and receivers
    info( "\nParsing 'mcastgroup' JSON input\n" )
    try:
        for i in range( 0, len( mcast_json['mcastgroup'] ) ):
            # Need to strip off netmask for src and grp
            src = getIpStripNetmask( mcast_json['mcastgroup'][i]['source_address'] )
            grp = getIpStripNetmask( mcast_json['mcastgroup'][i]['group_address'] )
            grpIp = grp.split( '.' )
            if ( ( int(grpIp[0]) < 224 ) or ( int(grpIp[0]) > 239 ) ):
                info( "Skipping grp %s as it is an invalid multicast address\n"
                      % grp )
                continue

            # Validate if connect point of sender is correct
            ingressSwName = mcast_json['mcastgroup'][i]['ingress_point']['McastConnectPoint']['elementId']
            # ingressSwName contains "of:000000000000000x"
            # Spilt to get portNumber at index 0 and y} at index 1
            ingressSwPort = mcast_json['mcastgroup'][i]['ingress_point']['McastConnectPoint']['portNumber']
            mininetSwName = getMininetSNameFromOnosUri( ingressSwName )

            debug( '''Checking if sender of group %s connected to
                   %s/%d is valid\n''' % (grp, mininetSwName, ingressSwPort ) )
            sendHost, sendIff = findNeighborFromSwitchPort( net,
                                                            mininetSwName,
                                                            ingressSwPort )
            if ( ( sendHost is not None ) and ( sendIff is not None ) ):
                # Validate that sendHost is a host (not switch etc.) and
                # has valid IP
                if( sendHost in net.hosts and sendIff.ip is not None ):
                    info( "%s has sender %s intf %s with IP %s\n"
                           % ( grp, sendHost.name, sendIff.name, sendIff.ip ) )
                    info( "\nAdding sender %s of group %s to senders list\n"
                          % ( src, grp ) )
                    addSenderOfGrp( grp, src )
                else:
                    info( '''Skipping grp %s as ingress_point is not correct
                             for sender %s\n''' % ( grp, src ) )
                    continue
            else:
                info( "Skipping elementId %s port %d as invalid in topology\n"
                      % ( ingressSwName, ingressSwPort ) )
                continue

            # Populate receivers from "egress_point": {"McastConnectPoint":[{elementId=of:0000000000000004, portNumber=4}]"
            egressConnectPoints = mcast_json['mcastgroup'][i]['egress_point']['McastConnectPoint']
            # Output of mcastConnectPoints is list of '{"elementId":"of:000000000000000x", "portNumber":y}'
            for j in range( 0, len( egressConnectPoints ) ):
                # Validate if connect point of receiver(s) is correct
                egressSwName = egressConnectPoints[j]['elementId']
                # ingressSwName contains "of:000000000000000x"
                # Spilt to get portNumber at index 0 and y} at index 1
                egressSwPort = mcast_json['mcastgroup'][i]['egress_point']['McastConnectPoint'][j]['portNumber'] 
                mininetSwName = getMininetSNameFromOnosUri( egressSwName )
                debug( '''Checking if receiver of group %s connected to %s/%d
                       is valid\n''' % ( grp, mininetSwName, egressSwPort ) )
                rcvHost, rcvIff = findNeighborFromSwitchPort( net,
                                                              mininetSwName,
                                                              egressSwPort )
                if ( ( rcvHost is not None ) and ( rcvIff is not None ) ):
                    # Validate that rcvHost is a host (not switch etc.) and
                    # has valid IP
                    if( rcvHost in net.hosts and rcvIff.ip is not None ):
                        info( "%s has receiver %s intf %s with IP %s\n"
                               % ( grp, rcvHost.name, rcvIff.name,
                                   rcvIff.ip ) )
                        addReceiverOfGrp( grp, rcvIff.ip )
                    else:
                        info( '''Skipping outPort elementId %s port %d as it's
                               not connected to host\n''' % ( egressSwName,
                                                              egressSwPort ) )
                else:
                    info( '''Skipping elementId %s port %d as it's invalid
                          in topology''' % ( egressSwName, egressSwPort ) )
    except ( ValueError, KeyError, TypeError ):
        error( "\nError in parsing 'mcastgroup' from %s...exiting\n"
               % mainArgs.input )
        net.stop()
        sys.exit()

    # TODO: Handle case such that controller could be down - for this warn
    # in checkListening() in node.py may need to changed to exception

    # Start sending mcast data from (S,G) present in grpSender dictionary
    groups = grpSender.keys()

    for group in groups:
        senderIp = grpSender[group]
        # Iterate through the interface list of all hosts to obtain interface
        # matching the senderIp and invoke the script from that host passing
        # appropriate interface, srcmac and srcip
        senderHost, ifName = findHostAndIfaceFromIp( net, senderIp )
        if ( senderHost is not None and ifName is not None ):
            debug( "Found host %s with iface %s matching senderIp %s\n"
                   % (senderHost.name, ifName, senderIp) )
            output( "\nStarting sender %s for group %s\n" % (senderIp, group) )
            senderHost.cmd( "sudo ./utils/mcastsend.py -grp %s -srcip %s  -iface %s -count 0 &"
                            % ( group, senderIp, ifName ) )
        else:
            debug( "Didn't find host with iface matching senderIp %s\n"
                   % ( senderIp ) )

    # Add receivers to group - this has to be replaced when JOIN is received
    # addReceiverToGrp( "239.0.0.1", "10.0.0.2" )

    i = 1

    output( "\nChecking receivers for reception of multicast data\n" )

    while ( ( i <= mainArgs.num ) or ( mainArgs.num == 0 ) ):
        try:

            info( "\nWaiting for %d seconds before starting next iteration\n"
                  % mainArgs.interval )
            sleep( mainArgs.interval )

            # Iterate over groups and over each receiver in group
            for grp, receiverList in grpReceivers.iteritems():

                # Get list of receivers of a particular group
                # receiversOfGrpList = getReceiversOfGrpIfAny(grp)

                popens = {}

                # for receiverIp in receiversOfGrpList:
                for receiverIp in receiverList:

                    debug( "ReceiverIp is %s\n" % receiverIp )

                    rcvHost, ifName = findHostAndIfaceFromIp( net, receiverIp )
                    if ( rcvHost is not None and ifName is not None ):
                        debug( '''Found host %s with iface %s matching
                                  receiverIp %s\n''' % ( rcvHost.name, ifName,
                                                         receiverIp ) )
                        debug( "\nStarting receiver %s for group %s\n"
                               % ( receiverIp, grp ) )
                        popens[rcvHost] = rcvHost.popen( "sudo ./utils/mcastreceive.py -grp %s -iface %s  -timeout %d -count %d"
                                                         % ( grp, ifName,
                                                             mainArgs.timeout,
                                                             mainArgs.count ) )

                # Monitor them and print the output
                for rcvHost, line in pmonitor(popens):
                    if rcvHost:
                        output( "%s %s%s" % ( time.ctime(), rcvHost.name,
                                              line ) )

            if( mainArgs.num != 0 ):
                output( "Completed Iteration %d\n" % i )
                i += 1

        except KeyboardInterrupt:
            output( "Received keyboard interrupt\n" )
            break

    output( "Stopping test\n" )
    if mainArgs.docli == True:
        CLI( net )

    net.stop()
    info( "\nDeleting the mcast-joins added from input JSON file\n" )
    groups = grpSender.keys()
    for group in groups:
        source = grpSender[ group ]
        src = source + '/32'
        grp = group + '/32'
        delURI = 'curl -X DELETE http://%s:%s/onos/mfwd/mcast/delete?src=%s\&grp=%s\r\n' \
                 % ( mainArgs.controller, mainArgs.port, src, grp )
        response = pexpect.run( delURI )
        if ( response.find( "Deleted flow for src %s and grp %s"
             % ( src, grp ) ) != -1 ):
            debug( "\nDeleted src %s, grp %s" % ( src, grp ) )
        else:
            debug( "\nFailed to delete src %s, grp %s\n" % ( src, grp ) )
    sys.exit()
