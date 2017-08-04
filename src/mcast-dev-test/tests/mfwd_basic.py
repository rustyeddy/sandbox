#!/usr/bin/python

import unittest
from os import path
from sys import path as sys_path
# get root of our test repo
sys_path.append(path.dirname(path.dirname(path.abspath(__file__))))
from lib.test_driver import TestDriver
from lib.test_logger import setup_logger

from topos.fourswmesh import FourSwMeshTopo
# IMPORT THE CONFIG FILE
from etc.config import *


source = '10.0.0.1/32'
group = '239.0.0.1/32'
ingress = 'of:0000000000000001/1'
egress = ['of:0000000000000002/2', 'of:0000000000000003/3', 'of:0000000000000004/4']

class MfwdBasicTest(unittest.TestCase):

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
        cls.mn = cls.td.mininet
        cls.cli = cls.td.onos_cli[0]
        cls.mfwd = cls.cli.onos_apps[MFWD_APP]

    @classmethod
    def tearDownClass(cls):
        """
        Cleans up anything remaining in the environment
        ends Onos Cli connection
        """
        cls.logger.info("Tearing Down Test")
        cls.cli.disconnect()
        cls.mn.stop()
        cls.logger.info("*"*30)
        cls.logger.info("Test Complete")
        cls.logger.info("*"*30)

    def test_01(self):
        """
        Ping All hosts to verify connectivity
        mcast-join (s, g) interfaces 1, 2, 3 (not 4)
        verify traffic is being received on hosts 2-3, but not 4
        """
        # self.logger.info("Verifying Mininet Ping All")
        # ping_missed_percent = self.mn.pingAll(timeout=30)
        # working = True if ping_missed_percent == 0 else False
        # self.assertTrue(working, "Unable to ping All Nodes, Ping Percent: " + str(ping_missed_percent))

        self.logger.info("Verifying Join on h11, h21, h31")
        join_status = self.mfwd.join_verify(source, group, ingress, egress[0:-1], use_json=True)
        self.assertTrue(join_status, "Join Failed on mcast-join h11, h21, h31")

        result, result_dict = self.td.send_and_rcv_traffic('h11', ['h21', 'h31', 'h41'], group)
        self.assertTrue(result_dict['h21'], "Failed to get traffic on node h21")
        self.assertTrue(result_dict['h31'], "Failed to get traffic on node h31")
        self.assertFalse(result_dict['h41'], "Received Traffic on node h41, when there shouldn't be traffic")

    def test_02(self):
        """
        Join on h11, h21, h31, h41
        verify traffic is received on all 4 receivers
        """
        self.logger.info("Verifying Join on h11, h21, h31, h41")
        join_status = self.mfwd.join_verify(source, group, ingress, egress, use_json=True)
        self.assertTrue(join_status, "Join Failed on mcast-join h11, h21, h31")

        result, result_dict = self.td.send_and_rcv_traffic('h11', ['h21', 'h31', 'h41'], group)
        self.assertTrue(result_dict['h21'], "Failed to get traffic on node h21")
        self.assertTrue(result_dict['h31'], "Failed to get traffic on node h31")
        self.assertTrue(result_dict['h41'], "Failed to get traffic on node h41")

    def test_03(self):
        """
        Delete egress to h21.
        Verify traffic has stopped.
        """
        self.logger.info("Verifying Delete on h21")
        delete_status = self.mfwd.delete(source, group, egress[0])
        self.assertTrue(delete_status, "Wasn't able to delete.")

        result, result_dict = self.td.send_and_rcv_traffic('h11', ['h21', 'h31', 'h41'], group)
        self.assertFalse(result_dict['h21'], "Received Traffic on node h21, when there shouldn't be traffic")
        self.assertTrue(result_dict['h31'], "Failed to get traffic on node h31")
        self.assertTrue(result_dict['h41'], "Failed to get traffic on node h41")

    def test_04(self):
        """
        Delete S,G
        Verify no traffic passes through.
        """
        self.logger.info("Verifying Delete on h21")
        delete_status = self.mfwd.delete(source, group)
        self.assertTrue(delete_status, "Wasn't able to delete.")

        result, result_dict = self.td.send_and_rcv_traffic('h11', ['h21', 'h31', 'h41'], group)
        self.assertFalse(result_dict['h21'], "Received Traffic on node h21, when there shouldn't be traffic")
        self.assertFalse(result_dict['h31'], "Received Traffic on node h31, when there shouldn't be traffic")
        self.assertFalse(result_dict['h41'], "Received Traffic on node h41, when there shouldn't be traffic")



if __name__ == '__main__':
    unittest.main(verbosity=2)
    suite = unittest.TestLoader().loadTestsFromTestCase(MfwdBasicTest)
