import json
import pexpect
from app_driver_base import AppDriverBase


class MfwdAppDriver(AppDriverBase):

    name = "org.onosproject.mfwd"

    mcast_join = "mcast-join"
    mcast_route_added = "Added the mcast route"
    mcast_show = "mcast-show"
    mcast_show_empty = "Mcast Route Table: 0 IPv4 Multicast Groups"
    mcast_show_header = "(<source>, <group>)"
    mcast_show_egress = "    ConnectPoint{elementId=<of>, portNumber=<pn>}"
    mcast_delete = "mcast-delete"
    mcast_delete_passed = "Successful delete"
    mcast_delete_failed = "Failed to delete"

    def __init__(self, handle, logger):
        """
        :param handle: pexpect object.
        """
        self.handle = handle
        self.logger = logger
        self.mcast_list = []

    def show(self, use_json=False, **kwargs):
        """
        Performs the mcast-show command
        :param use_json: option to get the json version
        :param kwargs: tbd
        :return: will return the response of the mcast-show as a raw string (unless json dictionary).
        """
        show = self.mcast_show+" -j" if use_json else self.mcast_show
        show_response = self.send_and_receive(show, self.onos_cli_running)
        if use_json:
            return json.loads(show_response[0])
        else:
            return show_response[0]

    def join(self, source, group, ingress=None, egress=None, proactive=False, expected=None):
        """
        Sends the join command with specified parameters
        :param source: source address
        :param group: multicast group address
        :param ingress: single ingress id and port ie "of:000000000001/1"
        :param egress: single or multiple(list) egress id(s) and port(s)
        :param expected: expected response string after sending mcast-join ONLY send one
        :return:
        """
        if (not isinstance(egress, list)):
            egress = [egress]
        expected = expected if expected else self.mcast_route_added

        cmd = self.mcast_join
        cmd += " -p" if proactive else ""
        cmd += " " + source + " " + group
        cmd += (" "+ingress) if ingress else ""
        for e in egress:
            cmd += " " + e

        result = self.send_and_receive(cmd,expected)
        if result[1] == 0:
            self.logger.debug("Response as expected")
            return True
        else:
            return False

    def delete(self, source="", group="", egress=None, expected=None):
        """
        Calls the delete mcast-delete command
        :param source: source address/
        :param group: multicast group address/
        :param expected: expected response string after sending mcast-delete ONLY send one
        :return: if expected value was found
        """
        command = self.mcast_delete + " " + source + " " + group
        expected = expected if expected else [self.mcast_delete_passed, self.mcast_delete_failed]

        if egress:
            if (not isinstance(egress, list)):
                egress = [egress]
            for e in egress:
                command += " " + e

        expected = self.onos_cli_running
        result = self.send_and_receive(command, expected)
        if result[1] == 0:
            self.logger.debug("Deleted Route or Response as expected")
            return True
        elif result[1] == 1:
            self.logger.debug("Deleted Route")
            return False
        else:
            self.logger.error("Something went wrong.")
            return None

    def join_verify(self, source="", group="", ingress="", egress="", use_json=True,  expected=None):
        """
        Method to mcast join and verify it exists in mcast-show
        :param source:
        :param group:
        :param ingress:
        :param egress:
        :param expected:
        :return:
        """
        join_sent = self.join(source, group, ingress, egress)
        if join_sent is False:
            return False
        else:
            if use_json:
                return self.in_show(source, group, ingress, egress, use_json=True)
            else:
                return self.in_show(source, group, ingress, egress)

    def delete_verify(self, source, group, egress=None):
        """
        Method to delete and verify it no longer exists in mcast-show
        :param source:
        :param group:
        :return:
        """
        self.delete(source, group, egress=egress)
        result = self.in_show(source, group, egress, use_json=True)
        if not result:
            return True
        else:
            return False

    def in_show(self, source="", group="", ingress="", egress="", use_json=True):
        # (192.168.52.99/32, 124.0.0.1/32)
        #   P2MP intent: not installed -
        #   ingress: ConnectPoint{elementId=of:000000000001, portNumber=1}
        #   egress: {
        #     ConnectPoint{elementId=of:000000000001, portNumber=1}
        #   }
        #   punted: 0
        # TODO really improve this, make checks for only source and group | s,g and ingress |everything
        if (not isinstance(egress, list)):
                egress = [egress]
        # Hnadle json parsing


        if use_json:
            json_dic = self.show(use_json=True)
            exists = False
            mcast_group_array = json_dic['mcastGroup']

            for mcast_group in mcast_group_array:
                json_source = mcast_group['sourceAddress']
                json_group = mcast_group['groupAddress']

                if (source==json_source) and (group==json_group):
                    exists = True
                    # Check if they only checked for S,G
                    if ingress=="" and egress[0]=="":
                        return exists
                    json_ingress_id = mcast_group['ingressPoint']['McastConnectPoint']['elementId']
                    json_ingress_port = mcast_group['ingressPoint']['McastConnectPoint']['portNumber']
                    ingress_id, ingress_port = self._convert_of_string(ingress)

                    # TODO make sure there can only ever be one to one with s/g and ingress
                    if (ingress_id == json_ingress_id) and (ingress_port == json_ingress_port):
                        # Check if they only supplied S,G and I
                        if egress[0]=="":
                            return exists
                        for e in egress:
                            e_id, e_port = self._convert_of_string(e)
                            json_egress = mcast_group['egressPoint']['McastConnectPoint']
                            egress_found = False

                            for json_e in json_egress:
                                json_e_id = json_e['elementId']
                                json_e_port = json_e['portNumber']

                                if (e_id == json_e_id) and (e_port == json_e_port):
                                    egress_found = True

                            exists = exists and egress_found
                    else:
                        exists = False

            return exists
        else:
            header = self._get_show_header(source, group)

            show_response = self.show()
            start_recording = False
            exact_command = []
            for line in show_response:
                if (start_recording and "punted:" in line):
                    exact_command.append(line)
                    start_recording = False
                elif start_recording:
                    exact_command.append(line)
                if (header in line):
                    exact_command.append(line)
                    start_recording = True

            exists = True

    def _get_show_header(self, source, group):
        """
        Creates the header of any show command for any particular s, g
        :param source: string of source address ie 192.168.52.99/32
        :param group: string of any group address ie 124.0.0.1/32
        :return: returns in format "(<source>, <group>)"
        """
        header = self.mcast_show_header.replace("<source>", source)
        header = header.replace("<group>", group)
        return header

    def _get_show_of_and_port(self, raw_of_strings):
        """
        takes in
        :param raw_of_strings:
        :return:
        """
        connect_point_list = []
        if (not isinstance(raw_of_strings, list)):
            raw_of_strings = [raw_of_strings]
        for string in raw_of_strings:
            of, port = self._convert_of_string(string)
            cp = self.mcast_show_egress.replace('<of>', of)
            cp = cp.replace('<pn>', port)
            connect_point_list.append(cp)

        return connect_point_list

    def _convert_of_string(self, raw_of_string):
        """
        remove surrounding whitespaces and grab of id and port
        :param raw_of_string:
        """
        of_string = raw_of_string.strip()
        of, port = of_string.split('/')
        return (of,int(port))