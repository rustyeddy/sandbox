from mininet.net import Mininet
from mininet.node import RemoteController


class NativeMininetDriver(Mininet):

    def __init__(self, topo, controller_ip, switch="OpenFlow13"):

        controller = lambda a: RemoteController(a, ip=controller_ip)
        super(NativeMininetDriver, self).__init__(controller=controller, topo=topo,  switch=switch)


