__author__ = 'julian'

from drivers.common.cli.onosclidriver import OnosCliDriver
from drivers.common.cli.mfwd import Mfwd

class OnosCliDriverPlus(OnosCliDriver):

    def __init__(self):
        self.apps = {}
        super(OnosCliDriverPlus, self).__init__()
        # Todo make adding apps dynamic, just doing this to get it done quickly
        self.apps[Mfwd.name] = Mfwd(self.handle)
        








