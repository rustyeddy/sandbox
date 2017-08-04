import inspect
import logging

from os import path
from sys import path as sys_path
from threading import Thread
from time import sleep, time
from mininet.cli import CLI
from mininet.util import pmonitor
from native_mininet_driver import NativeMininetDriver

from onosclidriverplus import OnosCliDriverPlus
from test_logger import setup_logger
# get root of our test repo
sys_path.append(path.dirname(path.dirname(path.abspath(__file__))))
from etc.config import *

class TestDriver(object):

    def __init__(self, topo, logger=None, verbosity="INFO"):

        self.logger = logger
        self.topo = topo
        self.onos = []
        self.onos_cli = []
        self.mininet = None

    def start_all(self):
        """
        This method is the general starting method.
        It will start up everything needed for the test.
        """
        self.logger.info("Starting All")
        # self.start_onos()
        self.start_onos_cli()
        self.start_mininet()

    def start_onos(self):
        pass

    def start_onos_cli(self):
        self.onos_cli.append(OnosCliDriverPlus(self.logger))
        self.onos_cli[-1].connect(name="onos_cli_"+str(len(self.onos_cli)), user_name=USER_NAME, pwd=PASSWORD, ip_address=ONOS_IP, port=PORT)
        return self.onos_cli[-1].startOnosCli()

    def start_mininet(self):
        self.mininet = NativeMininetDriver(self.topo, ONOS_IP)
        self.mininet.start()
        sleep(2)
        return True

    def send_and_rcv_traffic(self, source_host, recv_hosts, mcast_group, timeout=15):
        """
        This method will start multicast traffic from the source to all other hosts listed.
        :param source_host:
        :param recv_hosts:
        :return: if 0 packets were lost and no issues were found.
        """
        results = {}
        for host in recv_hosts:
            results[host] = False

        if (not isinstance(recv_hosts, list)):
            recv_hosts = [recv_hosts]

        source_host = self.mininet.nameToNode[source_host]

        sip = source_host.IP()

        recv_threads ={}
        for host in recv_hosts:
            h = self.mininet.nameToNode[host]
            recv_threads[h] = self.rx_traffic(h, mcast_group[0:-3], sip)

        self.logger.info('<%s>: %s' % ( source_host.name, source_host.pid))

        self.tx_traffic(source_host, mcast_group[0:-3])

        start_time = time()
        for h, line in pmonitor(recv_threads):#, timeoutms=500):

            if h:
                self.logger.info('<%s>: %s' % ( h.name, line ))
                if "lost: 0 dups: 0 out of order: 0" in line:
                    results[h.name] = True

            time_running = time() - start_time
            if time_running > timeout:
                break

        success = True
        for boolean in results.values():
            success = success and boolean

        return success, results

    def tx_traffic(self, source_host, mcast_group, count=10, packet_length=None):
        """
        Using source host on mininet and mcast group you can send multicast traffic.
        :param source_host: The host object or string name of it
        :param mcast_group: The mcast group  which we want to send traffic from.
        :param count: The
        :param packet_length:
        """
        source_host = self.mininet.nameToNode[source_host] if isinstance(source_host, basestring) else source_host

        cmd = "./mtraf -S -g " + mcast_group
        sip = source_host.IP()
        cmd = cmd + " -s " + sip + " -i " + sip
        if packet_length:
            cmd = cmd + " -l " + str(packet_length)
        if count:
            cmd = cmd + " -c " + str(count)
        #TODO add from config file mtraf location..
        # host.cmd("cd /home/dtvsdn/mtraf")
        self.logger.info(cmd)
        return source_host.popen(cmd, shell=True)

    def rx_traffic(self, rcv_host, mcast_group, source_ip):
        """
        Method to receive traffic on specified receiver.
        :param rcv_host: host string name.
        :param mcast_group: group to listen on.
        :return:
        """
        rcv_host = self.mininet.nameToNode[rcv_host] if isinstance(rcv_host, basestring) else rcv_host

        cmd = "./mtraf -R -g " + mcast_group
        # host = self.mininet.nameToNode[rcv_host]
        ip = rcv_host.IP()
        cmd = cmd + " -s " + source_ip + " -i " + ip
        #TODO add from config file mtraf location..
        # host.cmd("cd /home/dtvsdn/mtraf")
        self.logger.info(cmd)
        return rcv_host.popen(cmd, shell=True)


