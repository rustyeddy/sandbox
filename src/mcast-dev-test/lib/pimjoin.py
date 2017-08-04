#!/usr/bin/python

import logging
logging.getLogger( "scapy.runtime" ).setLevel( logging.ERROR )
from scapy.all import *
import argparse
import sys
from pim import *

import pprint

def pim_jp_create( **args ):
    "Create a pim JP message"
    
    eth = Ether(dst = "01:00:5e:00:00:0d", src = macaddr )
    ip  = IP( src = args['srcip'], dst = "224.0.0.13" )

    pim = PIM( type = 3 )
    pim.pimize(ip, eth)

    # Add group along with joinedsrcs and pruned srcs
    g1 = PIM_JoinPrune_Grp(grp="239.0.0.2",
                           joinedsrcs=[EncodedSource(src="10.0.0.1"),
                                       EncodedSource(src="10.0.0.2")],
                           prunedsrcs=[EncodedSource(src="10.0.0.3")])

    g2 = PIM_JoinPrune_Grp(grp="239.0.0.2",
                           prunedsrcs=[EncodedSource(src="10.0.0.3")])

    g3 = PIM_JoinPrune_Grp(grp="239.0.0.3",
                           joinedsrcs=[EncodedSource(src="10.0.0.2")],
                           prunedsrcs=[EncodedSource(src="10.0.0.3")])

    # Add required groups to JoinPrune packet
    jp =PIM_JoinPrune(neighbor = "10.1.4.44", mcastgroups = [g1, g2, g3] )

    return eth/ip/pim/jp
    

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description =
                                      'Send PIM Join/Prune packet' )
    parser.add_argument( '-iface', help='Sending interface name', required=True, type=str )
    parser.add_argument( '-srcip', help='Source IP Address', required=True, type=str )
    parser.add_argument( '-srcmac', help='Source MAC Address', required=False, type=str )
    parser.add_argument( '-count', help='Send count hellos every 60 seconds', required=False, type=int )

    args = parser.parse_args()

    macaddr = None
    if args.srcmac:
        macaddr = args.srcmac
    else:
        macaddr = "be:ef:ba:11:ca:fe"

    pkt = pim_jp_create( iface = args.iface, srcmac = macaddr, srcip = args.srcip )
    sendp( pkt )
