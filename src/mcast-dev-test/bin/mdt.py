#!/usr/bin/python

import sys
import getopt
import pprint
import argparse

from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.log import setLogLevel
from topos.mesh import MeshTopo
from topos.router import RouterTopo

argparse = argparse.ArgumentParser(description='Run ONOS on mininet demo.')

argparse.add_argument( '--controller', help='Specific the controller IP address',
                       required=False, type=str, default='127.0.0.1' )

margs = argparse.parse_args()

class McastDemo(MeshTopo):
    """
    The mcast mesh demo
    """

if __name__ == '__main__':

    controller = margs.controller

    setLogLevel( 'info')
    
    topo = MeshTopo()

    net = Mininet( controller=lambda a: RemoteController(a, ip=controller, protocols="OpenFlow13"), topo=topo )
    net.start()
    CLI( net )
    net.stop();
