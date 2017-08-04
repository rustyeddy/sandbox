#!/usr/bin/python

from scapy.all import *
import argparse
import time
import sys
import signal



def signal_term_handler( signal, frame ):
    print "got SIGTERM"
    sys.exit(0)

def rx_mcast(grp, iface, sport, dport, count, timeout):

    # TODO: Dst mac address should be generated from grpAddr
    grpAddr = "ip dst " + grp
    startTime = time.time()
    if( timeout ):
        result = sniff( filter=grpAddr, iface=iface, timeout=timeout,
                        count=count )
    else:
        result = sniff( filter=grpAddr, iface=iface, count=count )
    
    curTime = time.time()
    # If count is specified and if multicast data is not received, then time
    # will occur before count number of packets are received
    
    if( count > 0 ):
        if( len(result) < count and ( curTime - startTime ) >= timeout ):
            print " didn't receive %d packets for %s in %d secs" % ( count,
                                                                     grp,
                                                                     timeout )
        else:
            print " received %d packets for %s in %d secs" % ( count,
                                                               grp,
                                                               timeout )
    # Continuos reception
    else:
        print " received %d packets for %s in %d secs" % ( len( result ),
                                                           grp,
                                                           timeout )

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description =
                                  'Receive multicast data from group.' )
    parser.add_argument( '-grp', help='Group address', required=False, type=str,
                         default="239.0.0.1" )
    parser.add_argument( '-sport', help='Source port', required=False, type=int,
                         default=20433 )
    parser.add_argument( '-dport', help='Dest port', required=False, type=int,
                         default=20435 )
    parser.add_argument( '-count', help='''Receive number of packets and 0 for
                         infinity''', required=False, type=int, default=0 )
    parser.add_argument( '-iface', help='Receiving interface name', required=True,
                         type=str )
    parser.add_argument( '-timeout', help='Timeout in seconds', required=False,
                         type=int, default=10 )

    # Must run this next line is main.
    signal.signal( signal.SIGTERM, signal_term_handler )

    args = parser.parse_args()
    rx_mcast(args.grp, args.iface, args.sport, args.dport, args.count, args.timeout)