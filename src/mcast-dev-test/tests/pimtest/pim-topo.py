#!/usr/bin/python

import unittest
import wrapper

from pim import *

#!/usr/bin/python

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController, Node
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel

class Router( Node ):
    """
    A node with IP forwarding enabled.  NOTE: move this
    to it's own class in util.  I tried but keep getting
    an error that import can not find it.
    """

    def config(self, **params):
        super(Router, self).config(**params)
        # Enable forwarding on the router
        self.cmd('sysctl net.ipv4.ip_forward=1')

    def terminate(self):
        self.cmd('sysctl net.ipv4.ip_forward=0')
        super(Router, self).terminate()

class PimSimpleTopo(Topo):
    """A simple PIM Test topology:

    h1 -- s1 - r1 -- h2

    Where h1 & h2 are the mcast sender and reciever respectively. s1
    is the ONOS controlled OpenFlow switch.  r1 is the router running
    PIM with static routes.

    h1: 10.1.1.11/24 - r1: 10.1.1.1/24
    h2: 10.2.2.22/24 - r1: 10.2.2.1/24

    default routes will be created on r1 for both networks.
    IP_Forwarding must be set (or maybe not).
    """

    def build(self, **params):
        r1 = self.addNode('r1',
                          cls=Router,
                          ip="10.1.1.1/24",
                          mac="00:00:00:00:01:01")
        h1 = self.addHost('h1',
                          ip="10.1.1.11/24",
                          mac="00:00:00:00:01:11",
                          defaultRoute='via 10.1.1.1')
        self.addLink(r1, h1)

        s1 = self.addSwitch('s1', protocols="OpenFlow13")
        self.addLink(s1, r1,
                     intfName2='r1-eth1',
                     params2={'ip': '10.2.2.1/24'})

        s2 = self.addSwitch('s2', protocols="OpenFlow13")
        self.addLink(s1, s2)

        h2 = self.addHost('h2',
                          ip="10.2.2.22/24",
                          defaultRoute='via 10.2.2.1')
        self.addLink(s1, h2)

        h3 = self.addHost('h3',
                          ip='10.2.2.33/24',
                          defaultRoute='via 10.2.2.1')

        self.addLink(s2, h3)

        return self

topos = {'pimtopo': (lambda: PimSimpleTopo())}

class PIMTest(unittest.TestCase):

    iface = 'h4-eth0'
    count = 1

    @classmethod
    def setUpClass(cls):
        setUpLogger(cls)

    @classmethod
    def tearDownClass(cls):
        shutDownLogger(cls)

    def test_hello(self):
        self.logger.info("pim hello")
        eth = Ether()
        ip  = IP(src="10.1.1.14")
        pim = PIM()
        pim.pimize(ip, eth)

        sendp( eth/ip/pim, iface = iface, verbose = False, count = 1 )
        

    def test_join(self):
        self.logger.info("pim join")

def run():
    "Test linux router"
    topo = PimSimpleTopo()
    net = Mininet(topo=topo,
                  controller=lambda name: RemoteController(name,ip='127.0.0.1',
                                                           protocols="OpenFlow13"))
    # controller is used by s1-s3
    net.start()
    CLI( net )
    net.stop()

if __name__ == '__main__':
    setLogLevel( 'info' )
    run()

    



