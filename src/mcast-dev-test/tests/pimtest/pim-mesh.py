#!/usr/bin/python

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.log import setLogLevel

import pprint

class MeshTopo(Topo):
    """ Create some topologies that we can use to test various
    SDN topologies and schemes for our multicast use cases. 
    We have methods to create the following segments:

    h1 -- s1---s3 -- h3
           |\ /| 
           | X |  
           |/ \| 
    h2 -- s2 --s4 -- h4            
    
    """
    dpid = 1
    lastSwitch = 1
    lastHost = 1
    ofversion = "OpenFlow13"

    def build( self, nodes=4 ):
        "Build a mesh topology with hosts on each node"

        # Create the mesh
        ippref = "10.1.1.1"
        mesh = {}
        m = []

        for n in range( nodes ):
            nn = str(n+1) + str(n+1)
            nname = 's' + "%s" % ( n + 1 )
            mesh[nname] = self.addSwitch( nname, protocols=self.ofversion,
                                          dpid=self.getDpid() )
            m.append( mesh[nname] )
            h = self.addHost( "h%s" % ( n + 1 ) , 
                              ip="10.1." + str(n+1) + "." + nn + "/24",
                              mac="00:00:00:00:00:" + nn,
                              privateDirs=[ '/home/rusty/run/' + nname ] )
            print "Linking: " + mesh[nname] + " with " + h
            self.addLink( mesh[nname], h )

        # Now link all nodes in the mesh
        for i in range( 0, nodes-1 ):
            for j in range( i+1, nodes ):
                print "Linking " + m[i] + " with " + m[j]
                self.addLink( m[i], m[j] )
        return mesh

    ## get the dpid
    def getDpid( self ):
        try:
            dpid = hex( self.dpid )[ 2: ]

            self.dpid += 1
            return dpid
        except IndexError:
            raise Exception( 'Unable to derive default dpid ID - '
                             'please either specify a dpid or usa a '
                             'canonical switch name such as s32.' )

if __name__ == '__main__':
    setLogLevel( 'info' )
    
    topo = MeshTopo()
    net = Mininet( controller=lambda a: RemoteController(a, ip='127.0.0.1'),
                   topo=topo )
    net.start()
    CLI( net )
    net.stop()
