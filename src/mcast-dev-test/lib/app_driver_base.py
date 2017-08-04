import pexpect
import re
import json

class AppDriverBase(object):

    name = None
    onos_cli_running = "onos>"

    def __init__(self, handle, logger):
        """
        :param handle: pexpect object.
        """

        self.handle = handle
        self.logger = logger

    #TODO add something this generic to a ONOS_CLI_BASE class. do some nasty multiple inheritance for existing onos cli
    def send_and_receive(self, cmd_str, expected=None, timeout=30):
        """
        Send a string and get response (A more generic version than sendLine)
        :param cmd_str: Command string to send to onos cli
        :param expected: Send one or list of expected values
        :param timeout: amount of time to wait for expected response
        :return: a tuple containing 0: the response before the expected and 1: which expected result it was.
        """

        if (not isinstance(expected, list)):
            expected = [expected] if expected else [self.onos_cli_running]
        logStr = "\"Sending CLI command: '" + cmd_str + "'\""
        self.log(logStr)
        self.handle.sendline(cmd_str)
        expected_result = self.handle.expect(expected, timeout)
        response = self.handle.before
        # Remove ANSI color control strings from output
        ansiEscape = re.compile(r'\x1b[^m]*m')
        response = ansiEscape.sub('', response)
        # Remove extra return chars that get added
        response = re.sub(r"\s\r", "", response)
        # Strip excess whitespace
        response = response.strip()
        # parse for just the output, remove the cmd from response
        output = response.split(cmd_str.strip(), 1)
        response = output[1].strip()

        self.logger.debug("Response of:\n\t" + response)
        return (response, expected_result)

    def sendline(self, cmdStr, debug=False):
        """
        Send a completely user specified string to
        the onos> prompt. Use this function if you have
        a very specific command to send.

        Warning: There are no sanity checking to commands
        sent using this method.
        """
        try:
            logStr = "\"Sending CLI command: '" + cmdStr + "'\""
            self.log(logStr)
            self.handle.sendline(cmdStr)
            i = self.handle.expect(["onos>", "\$", pexpect.TIMEOUT])
            response = self.handle.before
            if i == 2:
                self.handle.sendline()
                self.handle.expect(["\$", pexpect.TIMEOUT])
                response += self.handle.before
                print response
                try:
                    print self.handle.after
                except TypeError:
                    pass
            # TODO: do something with i
            self.logger.info("Command '" + str(cmdStr) + "' sent to "
                             + self.name + ".")
            if debug:
                self.logger.debug(self.name + ": Raw output")
                self.logger.debug(self.name + ": " + repr(response))

            # Remove ANSI color control strings from output
            ansiEscape = re.compile(r'\x1b[^m]*m')
            response = ansiEscape.sub('', response)
            if debug:
                self.logger.debug(self.name + ": ansiEscape output")
                self.logger.debug(self.name + ": " + repr(response))

            # Remove extra return chars that get added
            response = re.sub(r"\s\r", "", response)
            if debug:
                self.logger.debug(self.name + ": Removed extra returns " +
                                  "from output")
                self.logger.debug(self.name + ": " + repr(response))

            # Strip excess whitespace
            response = response.strip()
            if debug:
                self.logger.debug(self.name + ": parsed and stripped output")
                self.logger.debug(self.name + ": " + repr(response))

            # parse for just the output, remove the cmd from response
            output = response.split(cmdStr.strip(), 1)
            if debug:
                self.logger.debug(self.name + ": split output")
                for r in output:
                    self.logger.debug(self.name + ": " + repr(r))
            return output[1].strip()
        except IndexError:
            self.logger.exception(self.name + ": Object not as expected")
            return None
        except TypeError:
            self.logger.exception(self.name + ": Object not as expected")
            return None
        except pexpect.EOF:
            self.logger.error(self.name + ": EOF exception found")
            self.logger.error(self.name + ":    " + self.handle.before)
        except Exception:
            self.logger.exception(self.name + ": Uncaught exception!")

    def log(self, cmdStr, level=""):
        """
            log  the commands in the onos CLI.
            returns True on success
            returns False if Error occurred
            Available level: DEBUG, TRACE, INFO, WARN, ERROR
            Level defaults to INFO
        """
        try:
            lvlStr = ""
            if level:
                lvlStr = "--level=" + level

            self.handle.sendline("")
            i = self.handle.expect(["onos>", "\$", pexpect.TIMEOUT])
            if i == 1:
                self.logger.error(self.name + ": onos cli session closed.")
            if i == 2:
                self.handle.sendline("")
                self.handle.expect("onos>")
            self.handle.sendline("log:log " + lvlStr + " " + cmdStr)
            self.handle.expect("log:log")
            self.handle.expect("onos>")

            response = self.handle.before
            if re.search("Error", response):
                return False
            return True
        except pexpect.TIMEOUT:
            self.logger.exception(self.name + ": TIMEOUT exception found")
        except pexpect.EOF:
            self.logger.error(self.name + ": EOF exception found")
            self.logger.error(self.name + ":    " + self.handle.before)
        except Exception:
            self.logger.exception(self.name + ": Uncaught exception!")