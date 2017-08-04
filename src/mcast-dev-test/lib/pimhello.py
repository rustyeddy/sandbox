#!/usr/bin/python

import logging
logging.getLogger( "scapy.runtime" ).setLevel( logging.ERROR )
from scapy.all import *
import argparse
import sys
from pim import *

def pim_hello_create( iface, srcmac, srcip ):
    "Create a PIM hello packet"

    eth = Ether(dst = "01:00:5e:00:00:0d", src = macaddr )
    ip  = IP( src = args.srcip, dst = "224.0.0.13" )

    pim = PIM()
    pim.pimize(ip, eth)

    # Example: All supported options
    opts = PIM_Hello_Options(options=[PIM_HelloOp_HoldTime(holdtime=120),
                                      PIM_HelloOp_LANPruneDelay(),
                                      PIM_HelloOp_DRPriority(priority=100),
                                      PIM_HelloOp_GenerationId(id=1000)])
    # PIM without any options
    return eth/ip/pim/opts


if __name__ == '__main__' :
    parser = argparse.ArgumentParser( description =
                                      'Send PIM Hello packet' )
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

    pkt = pim_hello_create(iface = args.iface, srcmac = macaddr, srcip = args.srcip)         

    sendp(pkt, iface=args.iface, verbose = False, count = 1 )
    sys.stdout.write( 'Sent 1 packet\r\n' )


