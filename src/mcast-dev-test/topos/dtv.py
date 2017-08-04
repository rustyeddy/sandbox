#!/usr/bin/python

from mininet.topo import Topo
from topo_plus import TopoPlus
from mininet.net import Mininet
from mininet.node import Controller, RemoteController, OVSSwitch
from mininet.cli import CLI
from mininet.util import dumpNodeConnections
from mininet.log import setLogLevel

import sys
import argparse
import pprint


class LCF(TopoPlus):
    """
    Create a representation of a LCF
    """
    topo_type = "lcf"
    lcf_number = 0

    def __init__(self, prefix, lcf_id, dtv_network=None, *args, **params):
        """
        :param lcf_id: A number or string to add at the ending of the topo name. If None will auto generate.
        :param prefix: IP prefix string ie "10.0."
        :param dtv_network: Able to pass in another TOPO to add on it's topology.
        :param params: add switch_count, encoder_count and of_version if needed.
        """
        if lcf_id is None: LCF.lcf_number += 1
        self._name_id = lcf_id if lcf_id else LCF.lcf_number
        self._prefix = prefix
        self.switch_list = []
        self.encoder_list = []
        super(LCF, self).__init__(*args, **params)

    def build(self, switch_count=2, encoder_count=8, of_version="OpenFlow13"):
        """
        Build the LCF
        :params should be passed in via the constructor.
        """
        # Create the primary and backup switches
        for i in range(1, switch_count+1):
            sw = self.addSwitch(self.name + '-' + 'sw' + str(i), dpid=self.get_dpid(), protocols=of_version)
            if i > 1:
                self.addLink(self.switch_list[-1], sw)
            self.switch_list.append(sw)
        # Now create all the controllers
        for e in range(1, encoder_count+1):
            ename = self.name + "-" + "en" + str(e)
            nipaddr = self.ip_addr_prefix + str(e)
            nmacaddr = self.get_dpid()

            e = self.addHost(ename, ip=nipaddr, mac=nmacaddr)
            self.encoder_list.append(e)
            # Link the encoder to switches
            for sw in self.switch_list:
                self.addLink(e, sw)

    def connect_pops(self, pop_p, pop_b):
        """
        connect lcf to pops
        :param pop_p: Primary POP
        :param pop_b: Backup POP
        """
        self.addLink(self.switch_list[0], pop_p.switch_list[0])
        self.addLink(self.switch_list[1], pop_b.switch_list[0])


class POP(TopoPlus):
    """
    Creates representation of a POP
    """
    topo_type = "pop"
    pop_number = 0

    def __init__(self, prefix, pop_id=None, use_router=False, *args, **kwargs):
        """
        Constructor to make POP
        :param pop_id: A number or string to add at the ending of the topo name. If None will auto generate.
        :param prefix: Ip prefix which can be passed in or auto generated.
        :param dtv_network: Able to pass in another TOPO to add on it's topology.
        :param kwargs: Able to pass in build parameters
        """
        if pop_id is None: POP.pop_number += 1
        self._name_id = pop_id if pop_id else POP.pop_number
        self._prefix = prefix
        self.switch_list = []
        self.crs_list = []
        self.host_list = []
        self.use_router =use_router
        super(POP, self).__init__(*args, **kwargs)

    def build(self, of_version="OpenFlow13", hybrid=False):
        """
        Build the POP
        """
        # creates the crs
        for i in range(2):
            # make switch
            switch_name = self.name + "-sw" + str(i)
            switch_name = self.addSwitch(switch_name, dpid=self.get_dpid(), protocols=of_version)
            self.switch_list.append(switch_name)

            if not hybrid:
                # make crs
                crs_name = self.name + "-" + "crs" + str(i)
                if self.use_router:
                    crs_name = self.addRouter(crs_name, mac=self.get_dpid())
                else:
                    crs_name = self.addSwitch(crs_name, dpid=self.get_dpid())
                self.crs_list.append(crs_name)
                #link the switch to crs
                self.addLink(switch_name, crs_name)

                # make host
                host_name = self.name + "-" + "h" + str(i)
                host_name = self.addHost(host_name, mac=self.get_dpid())
                self.host_list.append(host_name)
                # Link host and CRS
                self.addLink(host_name, crs_name)


        # connect crs to the other crs in pop
        self.addLink(self.switch_list[0], self.switch_list[1])
        if not hybrid:
            self.addLink(self.crs_list[0], self.crs_list[1])


class DirecTV(TopoPlus):
    """ 
    Create the topologies relevant to DIRECTV NBGN network
    and corresponding LCF development.
    """
    def __init__(self, *args, **params):
        self.pops = []
        self.lcfs = []
        super(DirecTV, self).__init__(*args, **params)

    def build(self, lcf_count=2, of_version="OpenFlow13", hybrid=False):
        """
        Build an interesting topology
        :return:
        """
        for i in range(lcf_count):
            lcf_to_add = LCF(lcf_id=None, prefix='10.'+ip_counter()+'.', of_version=of_version)
            self.lcfs.append(lcf_to_add)
            # add edges and nodes to dtv
            self.add_from_topo(lcf_to_add)

        for i in range(2):
            pop_to_add = POP(pop_id=None, prefix='10.'+ip_counter()+'.', of_version=of_version, hybrid=hybrid)
            self.pops.append(pop_to_add)
            # add edges and nodes to DTV
            self.add_from_topo(pop_to_add)


        # connect the pops in a ring
        if not hybrid:
            self.addLink(self.pops[0].crs_list[1], self.pops[1].crs_list[0])
            self.addLink(self.pops[0].crs_list[0], self.pops[1].crs_list[1])

        # connect lcfs to pops alternates primary and backup
        for i in range(len(self.lcfs)):
            self.addLink(self.lcfs[i].switch_list[0], self.pops[i%2].switch_list[0])
            self.addLink(self.lcfs[i].switch_list[1], self.pops[(i+1)%2].switch_list[1])
    #
    # def add_nodes_from(self, nodes):

    def __str__(self):
        str_buffer = ""
        for pop in self.pops:
            str_buffer += pop.__str__()
        for lcf in self.lcfs:
            str_buffer += lcf.__str__()
        return str_buffer


def static_vars(**kwargs):
    def decorate(func):
        for k in kwargs:
            setattr(func, k, kwargs[k])
        return func
    return decorate


@static_vars(id=0)
def ip_counter():
    ip_counter.id += 1
    return str(ip_counter.id)



if __name__ == '__main__':

    # command line args settings
    parser = argparse.ArgumentParser(description='Command line inputs for controller ip and switch type')
    parser.add_argument('-ofv', '--ofversion', help='a selection for OpenFlow Version. ie:OpenFlow13',
                        dest='of_version')
    parser.add_argument('-ip', '--controllerip', help='IP address of SDN controller', dest='controller_ip')
    parser.add_argument('-lc', '--lcfcount', help='Number of lcfs', type=int, dest='lcf_count')
    args = parser.parse_args()

    # if cmd line args not populated set them to defaults
    controller_ip = args.controller_ip if args.controller_ip else '127.0.0.1'
    of_version = args.of_version if args.of_version else "OpenFlow13"
    lcf_count = args.lcf_count if args.lcf_count else 2

    setLogLevel('info')

    topo = DirecTV(lcf_count, of_version)

    net = Mininet(topo=topo, controller=lambda a: RemoteController(a, ip=controller_ip))

    net.start()
    print(topo)
    print "Dumping host connections"
    dumpNodeConnections(net.hosts)
    print "Testing network connectivity"
    cli = CLI(net)
    cli.run()
    # net.pingAll()
    net.stop()
