#!/usr/bin/python

from scapy.all import *
import argparse
import sys
import signal




def signal_term_handler(signal, frame):
    print "got SIGTERM"
    sys.exit( 0 )



# TODO: Dst mac address should be generated from grpAddr
def tx_mcast(grp, srcip, iface, sport, dport, count):


    data = "Mulicast data frame"
    
    if( 0 == count ):
        while True:
            sendp( Ether( dst="01:00:5e:00:00:01" ) /
                   IP( dst=grp, src=srcip ) /
                   UDP( sport=sport, dport=dport ) / data,
                   iface=iface, verbose=False, count=1 )
    
    else:
        sendp( Ether( dst="01:00:5e:00:00:01" ) /
               IP( dst=grp, src=srcip ) /
               UDP( sport=sport, dport=dport ) / data,
               iface=iface, verbose=False, count=count )


if __name__ == '__main__':
    parser = argparse.ArgumentParser( description =
                                      'Send multicast data to group address.' )
    parser.add_argument( '-grp', help='Group address', required=True, type=str )
    parser.add_argument( '-srcip', help='Source IP', required=True, type=str )
    parser.add_argument( '-iface', help='Sending iface name', required=True,
                         type=str )
    parser.add_argument( '-sport', help='Source port', required=False, type=int,
                         default=20433 )
    parser.add_argument( '-dport', help='Dest port', required=False, type=int,
                         default=20435 )
    parser.add_argument( '-count', help='Send number of packets or 0 for infinite',
                         required=False, type=int, default=0 )

    # Must run this next line is main.
    signal.signal( signal.SIGTERM, signal_term_handler )

    args = parser.parse_args()
    tx_mcast(args.grp, args.srcip, args.iface, args.sport, args.dport, args.count)
