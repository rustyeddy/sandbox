#!/usr/bin/python

# Four switch meshed topology with a host for each switch

# host --- switch --- switch --- host
#            |     X     |
# host --- switch --- switch --- host

from mininet.topo import Topo
from mininet.node import OVSSwitch, RemoteController
from mininet.net import Mininet
from mininet.cli import CLI

class FourSwMeshTopo( Topo ):
    "Four switch mesh topology with n hosts connected to each switch"
    def build( self, n=1 ):
        # create the switches
        fourMeshSw = {}
        for i in range( 1, 5 ):
            j = i - 1
            fourMeshSw[j] = self.addSwitch( 'of%d' % i, cls=OVSSwitch )
            while( j > 0 ):
                self.addLink( 'of%d' % i , 'of%d' % j )
                j = j - 1

            # create n hosts for each switch and link them with switches
            for k in range( 1, n + 1 ):
                self.addHost( 'h%d%d' % ( i, k ) )
                self.addLink( 'h%d%d' % ( i, k ), 'of%d' % i )

if __name__ == '__main__':
    "Create network and test multicast packet delivery"
    net = Mininet( topo=FourSwMeshTopo( 1 ), switch=OVSSwitch,
                   controller=lambda a: RemoteController(a,
                                                         ip='192.168.56.100') )
    net.start()

    CLI( net )

    net.stop()
