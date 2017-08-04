#!/usr/bin/python
import sys
import pprint

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel
from topos.mesh import MeshTopo

class McastDemo(MeshTopo):
    """
    The mcast demo
    """

if __name__ == '__main__':
    setLogLevel( 'info' )
    
    topo = MeshTopo()
    net = Mininet( controller=lambda a: RemoteController(a, ip='127.0.0.1'),
                   topo=topo )
    net.start()
    CLI( net )
    net.stop();
