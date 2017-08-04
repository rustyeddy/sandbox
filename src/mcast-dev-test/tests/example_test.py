#!/usr/bin/python

import unittest
import logging
import time
from os import path
from sys import path as sys_path
from mininet.cli import CLI
from mininet.net import Mininet
from mininet.node import RemoteController
# get root of our test repo
sys_path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.test_driver import TestDriver
from lib.test_logger import setup_logger
from lib.onosclidriverplus import OnosCliDriverPlus

from topos.fourswmesh import FourSwMeshTopo
# IMPORT THE CONFIG FILE
from etc.config import *

source = '10.0.0.1/32'
group = '239.0.0.1/32'
ingress = 'of:0000000000000001/1'
egress = ['of:0000000000000002/2', 'of:0000000000000003/3', 'of:0000000000000004/4']
fourth_egress = 'of:0000000000000004/4'

class ExampleTest(unittest.TestCase):

    @classmethod
    def setUpClass(cls):
        """
        Assumes Onos is running with MFWD installed
        Connects to ONOS Controller host
        """
        cls.logger = setup_logger(__file__, verbosity="DEBUG")
        topo = FourSwMeshTopo()
        cls.td = TestDriver(topo, logger=cls.logger)
        cls.td.start_all()
        cls.td.onos_cli[0].activateApp(MFWD_APP)
        cls.mininet = cls.td.mininet
        cls.onos_cli = cls.td.onos_cli[0]
        cls.mfwd = cls.onos_cli.onos_apps[MFWD_APP]

    @classmethod
    def tearDownClass(cls):
        """
        Cleans up anything remaining in the environment
        ends Onos Cli connection
        """
        cls.logger.info("Tearing Down Test")
        cls.onos_cli.disconnect()
        cls.mininet.stop()
        cls.logger.info("*"*30)
        cls.logger.info("Test Complete")
        cls.logger.info("*"*30)

    def test_01(self):

        join_status = self.mfwd.join_verify(source, group, ingress, egress, use_json=True)
        #
        # self.logger.info("join_status " + join_status)

        result = self.td.send_and_rcv_traffic('h11', ['h21', 'h31', 'h41'], group)
        passed = "passed" if result else "failed"
        self.logger.info("Result " + passed)




if __name__ == '__main__':
    unittest.main(verbosity=2)
    suite = unittest.TestLoader().loadTestsFromTestCase(ExampleTest)
