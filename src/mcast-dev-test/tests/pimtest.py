#!/usr/bin/python

import unittest
import logging
import time
from os import path
from sys import path as sys_path

# Import standard mininet stuff
from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController
from mininet.cli import CLI
from mininet.log import setLogLevel

# get root of our test repo
sys_path.append(path.dirname(path.dirname(path.abspath(__file__))))

# load scapy stuff
from scapy.all import *
from lib.pim import PIM

from lib.test_logger import setup_logger
from topos.mesh import MeshTopo

# IMPORT THE CONFIG FILE
# from etc.config import *

class PIMTest(unittest.TestCase):
    "PIM Test Case"

    topo = None
    net = None
    iface = 'h4-eth0'
    count = 1

    @classmethod
    def setUpClass(self):
        topo = MeshTopo()
        self.net = Mininet(topo=self.topo,
                      controller=lambda name: RemoteController(name,ip='127.0.0.1',
                                                               protocols="OpenFlow13"))
        # controller is used by s1-s3
        self.net.start()

    @classmethod
    def tearDownClass(self):
        #self.net.stop()
        print ("FOOO")

    def test_hello(self):
        eth = Ether()
        ip  = IP(src="10.1.1.14")
        pim = PIM()
        pim.pimize(ip, eth)
        
        h = self.net.nameToNode['h4']
        h.command( sendp( eth/ip/pim, iface = self.iface, verbose = False, count = 1 )

if __name__ == '__main__':
    unittest.main()
    



