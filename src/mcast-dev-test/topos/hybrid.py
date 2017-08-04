#!/usr/bin/python

from mininet.topo import Topo
from topo_plus import TopoPlus
from mininet.net import Mininet
from mininet.node import Controller, RemoteController, OVSSwitch
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel
from mininet.log import info
from itertools import chain, groupby
from mininet.node import (Node, Host, OVSKernelSwitch, DefaultController,
                          Controller)
from dtv import LCF, POP, DirecTV
import sys
import argparse
import pprint


class DTV_Net(Mininet):

    def start(self):
        "Start controller and switches."
        if not self.built:
            self.build()
        info('*** Starting controller\n')
        for controller in self.controllers:
            info(controller.name + ' ')
            controller.start()
        info('\n')
        info('*** Starting %s switches\n' % len(self.switches))
        for switch in self.switches:
            info(switch.name + ' ')
            if (int(switch.name.split('-')[0][-1]) % 2):
                switch.start([self.controllers[0]])
            else:
                switch.start([self.controllers[1]])

        started = {}
        for swclass, switches in groupby(
                sorted(self.switches, key=type), type):
            switches = tuple(switches)
            if hasattr(swclass, 'batchStartup'):
                success = swclass.batchStartup(switches)
                started.update({s: s for s in success})
        info('\n')
        if self.waitConn:
            self.waitConnected()


if __name__ == '__main__':

    # command line args settings
    parser = argparse.ArgumentParser(
        description='Command line inputs for controller ip and switch type')
    parser.add_argument(
        '-ofv', '--ofversion', help='a selection for OpenFlow Version. ie:OpenFlow13',
        dest='of_version')
    parser.add_argument('-ip1', '--controllerip1',
                        help='IP address of SDN controller 1', dest='controller_ip1')
    parser.add_argument('-ip2', '--controllerip2',
                        help='IP address of SDN controller 2', dest='controller_ip2')
    parser.add_argument(
        '-lc', '--lcfcount', help='Number of lcfs', type=int, dest='lcf_count')
    args = parser.parse_args()

    # if cmd line args not populated set them to defaults
    controller_ip1 = args.controller_ip1 if args.controller_ip1 else '127.0.0.1'
    controller_ip2 = args.controller_ip2 if args.controller_ip2 else controller_ip1
    of_version = args.of_version if args.of_version else "OpenFlow13"
    lcf_count = args.lcf_count if args.lcf_count else 2

    setLogLevel('info')

    topo = DirecTV(lcf_count, of_version, True)

    ctrl1 = RemoteController('ctrl1', ip=controller_ip1)
    ctrl2 = RemoteController('ctrl1', ip=controller_ip2)

    net = DTV_Net(topo=topo, controller=[ctrl1, ctrl2])

    net.start()
    print(topo)
    print "Dumping host connections"
    dumpNodeConnections(net.hosts)
    print "Testing network connectivity"

    # connect to external network i.e, GNS3
    # assumed that the network is running in VM and launched by GNS3
    net.get('pop1-sw0').attach('minitap0')
    net.get('pop1-sw1').attach('minitap1')
    net.get('pop2-sw0').attach('minitap2')
    net.get('pop2-sw1').attach('minitap3')

    CLI(net)
    net.stop()
