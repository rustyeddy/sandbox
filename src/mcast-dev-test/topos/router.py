#!/usr/bin/python

from mininet.topo import Topo
from mininet.net import Mininet
from mininet.node import Controller, RemoteController, Node
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel

import pprint

class LinuxRouter( Node ):
    "A node with IP forwarding enabled."

    def config(self, **params):
        super(LinuxRouter, self).config(**params)
        # Enable forwarding on the router
        self.cmd('sysctl net.ipv4.ip_forward=1')

    def terminate(self):
        self.cmd('sysctl net.ipv4.ip_forward=0')
        super(LinuxRouter, self).terminate()

class RouterTopo(Topo):
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
        routers = {}
        m = []

        r1 = self.addNode("r1",
                          cls=LinuxRouter,
                          ip="10.11.1.1/24",
                          mac="00:00:00:00:11:01",
                          privateDirs=[ '/home/rusty/run/r1'])
        h1 = self.addHost("h1",
                          ip="10.11.1.11/24",
                          mac="00:00:00:00:11:11",
                          defaultRoute='via 10.11.1.1',
                          privateDirs=['/home/rusty/run/h1'])
        self.addLink(r1, h1)

        r2 = self.addNode("r2",
                          cls=LinuxRouter,
                          ip="10.12.1.1/24",
                          mac="00:00:00:00:12:01",
                          privateDirs=['/home/rusty/run/r2'])
        h2 = self.addHost("h2",
                          ip="10.12.1.22/24",
                          mac="00:00:00:00:12:22",
                          defaultRoute='via 10.12.1.1',
                          privateDirs=['/home/rusty/run/h2'])
        self.addLink(r2, h2)

        r3 = self.addNode("r3",
                          cls=LinuxRouter,
                          ip="10.13.1.1/24",
                          mac="00:00:00:00:13:01",
                          privateDirs=['/home/rusty/run/r3'])
        h3 = self.addHost("h3",
                          ip="10.13.1.33/24",
                          mac="00:00:00:00:13:33",
                          defaultRoute='via 10.13.1.1',
                          privateDirs=['/home/rusty/run/h3'])
        self.addLink(r3, h3)

        r4 = self.addNode("r4",
                          cls=LinuxRouter,
                          ip="10.14.1.1/24",
                          mac="00:00:00:00:14:01",
                          privateDirs=['/home/rusty/run/r4' ] )
        h4 = self.addHost("h4",
                          ip="10.14.1.44/24",
                          mac="00:00:00:00:14:44",
                          defaultRoute='via 10.14.1.1',
                          privateDirs=['/home/rusty/run/h4'])
        self.addLink(r4, h4)

        # Link the routers together
        self.addLink(r1, r2,
                     intfName1='r1-eth2', params1={'ip': '10.1.2.1/24'},
                     intfName2='r2-eth1', params2={'ip': '10.1.2.2/24'})

        self.addLink(r1, r3,
                     intfName1='r1-eth3', params1={'ip': '10.1.3.1/24'},
                     intfName2='r3-eth1', params2={'ip': '10.1.3.3/24'})

        self.addLink(r1, r4,
                     intfName1='r1-eth4', params1={'ip': '10.1.4.1/24'},
                     intfName2='r4-eth1', params2={'ip': '10.1.4.4/24'})

        self.addLink(r2, r3,
                     intfName1='r2-eth3', params1={'ip': '10.2.3.2/24'},
                     intfName2='r3-eth2', params2={'ip': '10.2.3.3/24'})

        self.addLink(r2, r4,
                     intfName1='r2-eth4', params1={'ip': '10.2.4.2/24'},
                     intfName2='r4-eth2', params2={'ip': '10.2.4.4/24'})

        self.addLink(r3, r4,
                     intfName1='r3-eth4', params1={'ip': '10.3.4.3/24'},
                     intfName2='r4-eth3', params2={'ip': '10.3.4.4/24'})

        # Now create some switches to connect to the routers
        s1 = self.addSwitch("s1", protocols="OpenFlow13",
                            dpid=self.getDpid())
        self.addLink(s1, r1, intfName2='r1-eth5', params2={'ip': '10.15.1.1/24'})
        self.addLink(s1, r2, intfName2='r2-eth5', params2={'ip': '10.15.1.2/24'})

        s2 = self.addSwitch("s2", protocols="OpenFlow13",
                            dpid=self.getDpid())
        self.addLink(s2, r1, intfName2='r1-eth6')
        self.addLink(s2, r2, intfName2='r2-eth6')
        self.addLink(s1, s2)

        s3 = self.addSwitch("s3", protocols="OpenFlow13",
                            dpid=self.getDpid())
        self.addLink(s3, s1)
        self.addLink(s2, s3)

        ## Now create the hosts that will connect to the switches
        h5 = self.addHost("h5",
                          ip="10.15.1.55/24",
                          mac="00:00:00:00:15:55",
                          defaultRoute='via 10.15.1.1',
                          privateDirs=['/home/rusty/run/h5'])
        h6 = self.addHost("h6",
                          ip="10.15.1.56/24",
                          mac="00:00:00:00:15:56",
                          defaultRoute='via 10.15.1.1',
                          privateDirs=['/home/rusty/run/h6'])
        self.addLink(s3, h5)
        self.addLink(s3, h6)

        return self

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

topos = { 'routertopo': ( lambda: RouterTopo() ) }

if __name__ == '__main__':
    setLogLevel( 'info' )
    
    topo = RouterTopo()
    net = Mininet(controller=lambda a: RemoteController(a, ip='127.0.0.1'),
                   topo=topo)
    net.start()
    CLI( net )
    net.stop();
