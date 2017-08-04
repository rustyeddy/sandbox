#
#   Class to help add with framework build out.
#   Base class for modularizing parts of the network.
#

from mininet.topo import Topo
from router import LinuxRouter


class TopoPlus(Topo):
    """
    This class will be used to add a layer between our classes and the base topo
    Allowing us to add specific updates here across all topos for the purpose of making a test framework.
    """

    # Topo Type should define what the structure is. ie POP/LCF etc...
    topo_type = ""
    # Gives a unique number per
    topo_number = 0
    # Last part for name
    _name_id = ""
    #First parts of ip
    _prefix = ""
    # Define mac address counter, we don't want
    mac_counter = 0

    # Generic lists
    switch_list = None
    host_list = None
    router_list = None
    # DTV specific lists
    encoder_list = None
    crs_list = None

    def __call__(self):
        """
        Returns self when instance is called like a function.
        :return: self
        """
        return self

    @property
    def name(self):
        """
        Used to generate cls.name make sure to define topo_type and _name_ide
        in subclass otherwise an empty string will be returned.
        :return: Name of the Topo
        """
        return self.topo_type + str(self._name_id)

    @property
    def ip_addr_prefix(self):
        """
        Used to generate the ip prefix
        :return: the ip
        """
        return self._prefix + str(self._name_id) + "."

    def addRouter(self, name, **kwargs):
        """
        Add's a router using the LinuxRouter Class
        :return:
        """
        return self.addNode(name=name, cls=LinuxRouter, **kwargs)

    def add_nodes_from(self, node_tuples):
        """
        Can be used to generate graphics with networkx
        :param node_tuples:
        :return:
        """
        pass

    def add_edges_from(self, edge_tuples):
        """
        can be used to generate graphics with networkx
        :param edge_tuples:
        :return:
        """
        pass

    def add_from_topo(self, topo):
        self.g.edge.update(topo.g.edge)
        self.g.node.update(topo.g.node)
        self.ports.update(topo.ports)

    @staticmethod
    def get_dpid():
        """
        Generates a unique dpid as long as only this method is used.
        """
        TopoPlus.mac_counter += 1
        dpid = hex(TopoPlus.mac_counter)[2:]
        dpid = '0' * (16 - len(dpid)) + dpid
        return dpid

    def __str__(self):
        """
        Returns a string of the elements on it's topology.
        """
        str_buffer = ""
        str_buffer += "\n*********************************"
        str_buffer += "\n" +self.topo_type.upper()+ ": " + self.name
        str_buffer += "\n*********************************"

        if self.host_list:
            str_buffer += "\n\tHosts:"
            for hst in self.host_list:
                str_buffer += "\n\t\t" + hst

        if self.switch_list:
            str_buffer += "\n\tSwitches:"
            for sw in self.switch_list:
                str_buffer += "\n\t\t" + sw

        if self.router_list:
            str_buffer += "\n\tRouters:"
            for rt in self.router_list:
                str_buffer += "\n\t\t" + rt

        if self.encoder_list:
            str_buffer += "\n\tEncoders:"
            for en in self.encoder_list:
                str_buffer += "\n\t\t" + en

        if self.crs_list:
            str_buffer += "\n\tCRS:"
            for crs in self.crs_list:
                str_buffer += "\n\t\t" + crs

        return str_buffer