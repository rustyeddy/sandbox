#!/usr/bin/python

import unittest
import logging
import time

# Bring in system related things from the path
from os import path
from sys import path as sys_path

# Bring in Mininet stuff
from mininet.cli import CLI
from mininet.net import Mininet
from mininet.node import RemoteController

# Get root of our test repo
sys_path.append(path.dirname(path.dirname(path.abspath(__file__))))

# Bring in some test libs
from lib.test_driver import TestDriver
from lib.test_logger import setup_logger
from lib.onosclidriverplus import OnosCliDriverPlus

# Bring in our topology
from topos.mesh import MeshTopo

group  = 227.1.1.1
source = 10.1.1.11
recvr2 = 10.1.1.22
recvr3 = 10.1.1.33
recvr4 = 10.1.1.44

class PIMTest(unittest.TestCase):

    @classmethod
    def setUpClass(cls):
        """
        Assumes ONOS is running with MFWD and PIM installed.
        Connects to the host controller.
        """

        cls.logger = setup_logger(__file__, verbosity = "DEBUG")
        topo = MeshTopo()
        cls.td = TestDriver(topo, logger = cls.logger)

        cls.td.start_all()
        cls.td.onos_cli[0].activateApp(MFWD_APP)
        cls.td.onos_cli[0].activateApp(PIM_APP)

        cls.mininet = cls.td.mininet
        cls.onos_cli = cls.td.onos_cli[0]
        cls.mfwd = cls.onos_cli.onos_apps[MFWD_APP]
        cls.pim = cls.onos_cli.onos_apps[PIM_APP]

    def tearDownClass(cls):
        """
        Cleans up the environment.
        """

        cls.logger.info("Tearing down test")
        cls.onos_cli.disconnect()
        cls.mininet.stop()
        cls.logger.info("*"*30)
        cls.logger.info("Testing complete")
        cls.logger.info("*"*30)

    def test_config(self):
        
        
