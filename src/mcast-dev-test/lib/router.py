#!/usr/bin/python

from mininet.node import Node
import os

"""
router.py: Mininet router class

This can be used for creating a router from a mininet node.
"""


class Router(Node):
    "A node that forwards IP traffic."

    routerPath = None

    def __init__(self):
        self.stopForwarding = False

    def config(self, **params):
        super(Router, self).config(**params)
        # Enable IP forwarding
        self.cmd('sysctl net.ipv4.ip_forward=1')

    def terminate(self):
        if self.stopForwarding:
            self.cmd('sysctl net.ipv4.ip_forward=0')
        super(Router, self).terminate()


class PimRouter(Router):
    """USC PIMD"""

    def __init__(self):
        super.__init__()
        home = os.getenv("HOME")
        pimdpath = home + "/pimd/pimd"
        if os.path.exists(pimdpath):
            self.routerPath = pimdpath
