#!/usr/bin/python

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.log import setLogLevel

import pprint

class host2host(Topo):

    def build( self ):
        "Build a host2host topology"

        # Create the mesh
        ippref = "10.1.1.1"

        h1 = self.addHost( "h1", ip="10.1.1.1/24",
                           mac="00:00:00:00:00:11" )
        h2 = self.addHost( "h2", ip="10.1.1.2/24",
                           mac="00:00:00:00:00:22" )

        self.addLink( h1, h2 )
        return

if __name__ == '__main__':
    setLogLevel( 'info' )
    
    topo = host2host()
    net = Mininet( controller=lambda a: RemoteController(a, ip='127.0.0.1'),
                   topo=topo )
    net.start()
    CLI( net )
    net.stop();
