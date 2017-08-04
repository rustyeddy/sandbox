#!/usr/bin/python

from scapy.all import *
import argparse
import sys
import signal

parser = argparse.ArgumentParser( description='UDP send unicast data.' )

parser.add_argument( '-dstip', help='Destination IP', required=True, type=str )
parser.add_argument( '-srcip', help='Source IP', required=True, type=str )
parser.add_argument( '-iface', help='Sending iface name', required=True,
                     type=str )
parser.add_argument( '-sport', help='Source port', required=False, type=int,
                     default=20433 )
parser.add_argument( '-dport', help='Dest port', required=False, type=int,
                     default=20435 )
parser.add_argument( '-count', help='Send number of packets or 0 for infinite',
                     required=False, type=int, default=0 )

args = parser.parse_args()

data = "Unicast data frame"

def signal_term_handler(signal, frame):
    print "got SIGTERM"
    sys.exit( 0 )

signal.signal( signal.SIGTERM, signal_term_handler )

# TODO: Dst mac address should be generated from grpAddr

if( 0 == args.count ):
    while True:
        try:
            sendp( Ether( dst="01:02:03:04:05:06", src="11:22:33:44:55:66") /
                   IP( dst=args.dstip, src=args.srcip ) /
                   UDP( sport=args.sport, dport=args.dport ) / data,
                   iface=args.iface, verbose=False, count=1 )

        except KeyboardInterrupt:
            print "Received keyboard interrupt\n"
            sys.exit( 0 )

else:
    try:
        sendp( Ether( dst="01:02:03:04:05:06", src="11:22:33:44:55:66" ) /
               IP( dst=args.dstip, src=args.srcip ) /
               UDP( sport=args.sport, dport=args.dport ) / data,
               iface=args.iface, verbose=False, count=args.count )

    except KeyboardInterrupt:
        print"Received keyboard interrupt\n"
        sys.exit( 0 )
