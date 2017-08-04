#!/usr/bin/python

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel

##
## Borrowed from: https://github.com/bjlee72/IRIS/wiki/Tutorial-101-mininet-%28English%29
##
def int2dpid( dpid ):
    try:
        dpid = hex( dpid )[ 2: ]
        dpid = '0' * ( 16 - len( dpid ) ) + dpid
        return dpid
    except IndexError:
        raise Exception( 'Unable to derive default dpid ID - '
                         'please either specify a dpid or usa a '
                         'canonical switch name such as s32.' )

###
###   Current (non-sdn LCS topology 
###
###   e === lp === cp ... core --- rcv
###     \                         /
###      \                       /
###       + lb === cb ... core +
###
###
### You can supply as many encoders as you like
###
def dtvLCSTest():
    "Create and test the simple LCS topology"

    topos = {}
    ## net = Mininet(topo)    
    net = Mininet( controller=lambda a: RemoteController(a, ip='10.0.2.2'), topo=None )
    net.addController( 'c0' )

    # Receiver
    rcv = net.addHost( 'rcv', ip='10.1.3.3' )

    # The core
    core = net.addSwitch('core', dpid=int2dpid(5) )

    # Connect the receiver to the coreh
    net.addLink(rcv, core)

    cp = net.addSwitch('cp', dpid=int2dpid(1) )
    cb = net.addSwitch('cb', dpid=int2dpid(2) )
    net.addLink(cp, core)
    net.addLink(cb, core)
    net.addLink(cp, cb)

    # Primary and backup routers
    lp = net.addSwitch('lp', dpid=int2dpid(3) )
    lb = net.addSwitch('lb', dpid=int2dpid(4) )

    net.addLink(lp, cp)
    net.addLink(lb, cb)

    # Now create all the hosts
    n = 4
    for e in range(n):
        ipaddr = '10.1.1.' + str(e + 1)
        print "IP: " + ipaddr 

        enc = net.addHost('e' + str(e + 1), ip=ipaddr)

        # Add the primary and backup links
        net.addLink(enc, lp)
        net.addLink(enc, lb)

    net.configHosts()        
    net.start()

    print "Dumping host connections"
    dumpNodeConnections(net.hosts)

    print "Testing network connectivity"
    CLI( net );

    net.stop()


if __name__ == '__main__':
    # Tell mininet to print useful information
    setLogLevel('debug')
    dtvLCSTest()
