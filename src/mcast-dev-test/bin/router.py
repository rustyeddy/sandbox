#!/usr/bin/python
import sys
import pprint

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Node, Controller, RemoteController
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel

class Router( Node ):
    "Create a router that can run quagga"

    def config( self, **params ):
        super( Router, self).config( **params )

        # Enable Forwarding on the router
        self.cmd( 'sysctl net.ipv4.ip_forward=1' )

    def terminate( self ):
        super( Router, self ).terminate()


class RouterTopo( Topo ):
    """
    Create a host that will act as a router connected to a switch and
    another host.

              r2 +-- s1 -- h1
             / |      |  
      h1---r2  |      |
             \ |      |
              r1 +-- s2 -- h2

    """

    def build( self, **_opts ):
        
        r1 = self.addNode( 'r1', cls=Router, ip='10.1.1.254/24' )
        r2 = self.addNode( 'r2', cls=Router, ip='10.1.2.254/24' )
        r3 = self.addNode( 'r3', cls=Router, ip='10.1.3.254/24' )

        s1, s2 = [ self.addSwitch( s ) for s in 's1', 's2' ]

        h0 = self.addHost( 'h0', ip='10.1.1.10/24', defaultRoute='via 10.1.1.254' )
        h1 = self.addHost( 'h1', ip='10.1.2.11/24', defaultRoute='via 10.1.2.254' )
        h2 = self.addHost( 'h2', ip='10.1.2.22/24', defaultRoute='via 10.1.2.254' )
        h3 = self.addHost( 'h3', ip='10.1.2.2/24', defaultRoute='via 10.1.2.254' )

        self.addLink( h0, r, intfName2='r0-eth0', params2={ 'ip' : '10.1.1.253/24' } )
        self.addLink( s1, r, intfName2='r0-eth2' )
        self.addLink( s2, r, intfName2='r0-eth1', params2={ 'ip' : '10.1.2.254/24' } )

        self.addLink( h1, s1 )
        self.addLink( h2, s2 )
        

def run():
    "Test the router running quagga"
    topo = RouterTopo()
    net = Mininet( topo=topo ) # add onos
    net.start();

    info( '*** Route Table on Router\n' )
    print net[ 'r0' ].cmd( 'route' )
    CLI( net )
    net.stop()
    
if __name__ == '__main__':
    setLogLevel( 'info' )
    
    topo = RouterTopo()
    net = Mininet( controller=lambda a: RemoteController(a, ip='127.0.0.1'),
                   topo=topo )
    net.start()
    CLI( net )
    net.stop();
