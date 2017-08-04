#
# Driver to simulate the onos cli driver and apps.
#
import pexpect

from onosclidriver import OnosCliDriver
from mfwd_app_driver import MfwdAppDriver


class OnosCliDriverPlus(OnosCliDriver):

    # string constants used to remove hard coding in every pexepect call.
    # (might move these to specific functions if they aren't applicable to multiple functions.)
    start_cli = "client -u karaf"
    onos_cli_running = "onos>"

    # TODO make this dynamic so we can grab from the module rather than hard coding.
    _app_lookup = {
        'org.onosproject.mfwd' : MfwdAppDriver
    }

    def __init__(self, logger):

        # apps dictionary
        self.onos_apps = {}
        self.logger = logger
        super(OnosCliDriverPlus, self).__init__(self.logger)

    def startOnosCli(self, karafTimeout="", commandlineTimeout=10, onosStartTimeout=60):
        """
        karafTimeout is an optional argument. karafTimeout value passed
        by user would be used to set the current karaf shell idle timeout.
        Note that when ever this property is modified the shell will exit and
        the subsequent login would reflect new idle timeout.
        Below is an example to start a session with 60 seconds idle timeout
        ( input value is in milliseconds ):

        tValue = "60000"
        main.ONOScli1.startOnosCli( ONOSIp, karafTimeout=tValue )

        Note: karafTimeout is left as str so that this could be read
        and passed to startOnosCli from PARAMS file as str.
        """
        try:
            self.handle.sendline("")
            x = self.handle.expect([
                "\$", self.onos_cli_running], commandlineTimeout)

            if x == 1:
                self.logger.info("ONOS cli is already running")
                return True

            # Wait for onos start ( -w ) and enter onos cli
            self.handle.sendline(self.start_cli)
            i = self.handle.expect([
                self.onos_cli_running,
                pexpect.TIMEOUT], onosStartTimeout)

            if i == 0:
                self.logger.info(" CLI Started successfully")
                if karafTimeout:
                    self.handle.sendline(
                        "config:property-set -p org.apache.karaf.shell\
                                 sshIdleTimeout " +
                        karafTimeout)
                    self.handle.expect("\$")
                    self.handle.sendline(self.start_cli)
                    self.handle.expect(self.onos_cli_running)
                return True
            else:
                # If failed, send ctrl+c to process and try again
                self.logger.info("Starting CLI failed. Retrying...")
                self.handle.send("\x03")
                self.handle.sendline(self.start_cli)
                i = self.handle.expect([self.onos_cli_running, pexpect.TIMEOUT],
                                       timeout=30)
                if i == 0:
                    self.logger.info(" CLI Started " +
                                     "successfully after retry attempt")
                    if karafTimeout:
                        self.handle.sendline(
                            "config:property-set -p org.apache.karaf.shell\
                                    sshIdleTimeout " +
                            karafTimeout)
                        self.handle.expect("\$")
                        self.handle.sendline(self.start_cli)
                        self.handle.expect(self.onos_cli_running)
                    return True
                else:
                    self.logger.error("Connection to CLI timeout")
                    return False

        except TypeError:
            self.logger.exception(self.name + ": Object not as expected")
            return None
        except pexpect.EOF:
            self.logger.error(self.name + ": EOF exception found")
            self.logger.error(self.name + ":    " + self.handle.before)
        #           main.cleanup()
        #           main.exit()
        except Exception:
            self.logger.exception(self.name + ": Uncaught exception!")
        #           main.cleanup()
        #           main.exit()

    def activateApp(self, app_name, check=True):
        """
        Adding it to cli objects apps list.
        """
        app_available = super(OnosCliDriverPlus, self).activateApp(app_name, check)
        if app_available == True:
            self.onos_apps[app_name] = self._app_lookup[app_name](self.handle, self.logger)
        return app_available

    def deactivateApp(self, app_name, check=True):
        """
        deactivates app and removes from app list
        """
        app_deactivated = super(OnosCliDriverPlus, self).deactivateApp(app_name, check)
        if app_deactivated == True:
            self.onos_apps.pop(app_name)
        return app_deactivated
